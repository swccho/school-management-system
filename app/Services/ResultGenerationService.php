<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSubjectConfig;
use App\Models\GradeScale;
use App\Models\GradeScaleItem;
use App\Models\MarkEntry;
use App\Models\ResultSummary;
use App\Models\ResultSubjectDetail;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class ResultGenerationService
{
    /**
     * Generate result summaries and subject details for an exam.
     * Optional class_id and section_id filter which mark entries (and thus which students) are included.
     * GPA = average of subject grade points. Pass = all subjects pass. Merit = rank by obtained_marks.
     */
    public function generate(
        int $examId,
        ?int $classId = null,
        ?int $sectionId = null,
        ?int $gradeScaleId = null
    ): array {
        $exam = Exam::find($examId);
        if (! $exam) {
            return ['generated' => 0, 'message' => 'Exam not found.'];
        }

        $gradeScale = $gradeScaleId
            ? GradeScale::with('gradeScaleItems')->find($gradeScaleId)
            : GradeScale::where('is_default', true)->with('gradeScaleItems')->first();
        if (! $gradeScale || $gradeScale->gradeScaleItems->isEmpty()) {
            return ['generated' => 0, 'message' => 'No grade scale or grade scale items found.'];
        }

        $query = MarkEntry::where('exam_id', $examId)
            ->with(['markEntryItems', 'subject', 'student']);
        if ($classId !== null) {
            $query->where('class_id', $classId);
        }
        if ($sectionId !== null) {
            $query->where('section_id', $sectionId);
        }
        $markEntries = $query->get();

        $byStudent = $markEntries->groupBy('student_id');
        if ($byStudent->isEmpty()) {
            return ['generated' => 0, 'message' => 'No marks found for the selected context.'];
        }

        $school = School::first();
        $configsCache = [];

        DB::transaction(function () use (
            $examId,
            $byStudent,
            $gradeScale,
            $school,
            &$configsCache
        ) {
            $studentIds = $byStudent->keys()->all();
            ResultSummary::where('exam_id', $examId)->whereIn('student_id', $studentIds)->delete();

            $summariesWithObtained = [];
            foreach ($byStudent as $studentId => $entries) {
                $summary = $this->buildSummaryForStudent(
                    (int) $studentId,
                    $entries,
                    $gradeScale,
                    $examId,
                    $school,
                    $configsCache
                );
                if ($summary !== null) {
                    $summariesWithObtained[] = $summary;
                }
            }

            $this->assignMeritPositions($summariesWithObtained);

            foreach ($summariesWithObtained as $data) {
                $summary = ResultSummary::create($data['summary']);
                foreach ($data['details'] as $detail) {
                    $detail['result_summary_id'] = $summary->id;
                    $detail['school_id'] = $school?->id;
                    ResultSubjectDetail::create($detail);
                }
            }
        });

        return ['generated' => $byStudent->count(), 'message' => 'Results generated.'];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, MarkEntry>  $entries
     * @param  array<int, ExamSubjectConfig>  $configsCache
     * @return array{summary: array, details: array}|null
     */
    private function buildSummaryForStudent(
        int $studentId,
        $entries,
        GradeScale $gradeScale,
        int $examId,
        ?School $school,
        array &$configsCache
    ): ?array {
        $totalMarks = 0.0;
        $obtainedMarks = 0.0;
        $details = [];
        $gradePoints = [];

        foreach ($entries as $markEntry) {
            $config = $this->getSubjectConfig(
                $examId,
                $markEntry->class_id,
                $markEntry->subject_id,
                $configsCache
            );
            if (! $config) {
                continue;
            }
            $fullMarks = (float) $config->full_marks;
            $passMarks = (float) $config->pass_marks;
            $obtained = (float) $markEntry->markEntryItems->sum('obtained_marks');

            $totalMarks += $fullMarks;
            $obtainedMarks += $obtained;

            $percentage = $fullMarks > 0 ? ($obtained / $fullMarks) * 100 : 0;
            $gradeItem = $this->findGradeItem($gradeScale, $percentage);
            $gradeLetter = $gradeItem?->letter_grade;
            $gradePoint = $gradeItem ? (float) $gradeItem->grade_point : 0;
            $subjectPass = $obtained >= $passMarks ? 'pass' : 'fail';
            $gradePoints[] = $gradePoint;

            $details[] = [
                'subject_id' => $markEntry->subject_id,
                'full_marks' => $fullMarks,
                'obtained_marks' => $obtained,
                'grade_letter' => $gradeLetter,
                'grade_point' => $gradePoint,
                'pass_status' => $subjectPass,
                'remarks' => null,
            ];
        }

        if (empty($details)) {
            return null;
        }

        $overallPass = ! in_array('fail', array_column($details, 'pass_status'), true);
        $gpa = count($gradePoints) > 0 ? array_sum($gradePoints) / count($gradePoints) : null;
        $overallPercentage = $totalMarks > 0 ? ($obtainedMarks / $totalMarks) * 100 : 0;
        $overallGradeItem = $this->findGradeItem($gradeScale, $overallPercentage);

        $summary = [
            'school_id' => $school?->id,
            'exam_id' => $examId,
            'student_id' => $studentId,
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'gpa' => round($gpa, 2),
            'letter_grade' => $overallGradeItem?->letter_grade,
            'merit_position' => null,
            'pass_status' => $overallPass ? 'pass' : 'fail',
            'published_status' => false,
            'remarks' => null,
            'generated_at' => now(),
        ];

        return [
            'summary' => $summary,
            'details' => $details,
            'obtained_marks' => $obtainedMarks,
        ];
    }

    private function getSubjectConfig(int $examId, int $classId, int $subjectId, array &$cache): ?ExamSubjectConfig
    {
        $key = "{$examId}-{$classId}-{$subjectId}";
        if (! isset($cache[$key])) {
            $cache[$key] = ExamSubjectConfig::where('exam_id', $examId)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->first();
        }

        return $cache[$key];
    }

    private function findGradeItem(GradeScale $gradeScale, float $percentage): ?GradeScaleItem
    {
        foreach ($gradeScale->gradeScaleItems as $item) {
            if ($item->containsMark($percentage)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{summary: array, obtained_marks: float}>  $summariesWithObtained
     */
    private function assignMeritPositions(array &$summariesWithObtained): void
    {
        usort($summariesWithObtained, fn ($a, $b) => $b['obtained_marks'] <=> $a['obtained_marks']);
        $position = 1;
        foreach ($summariesWithObtained as &$data) {
            $data['summary']['merit_position'] = $position++;
        }
    }
}
