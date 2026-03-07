<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamClassConfig;
use App\Models\ExamSubjectConfig;
use App\Models\MarkComponent;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class ExamService
{
    public function createExamWithConfigs(array $validated): Exam
    {
        return DB::transaction(function () use ($validated) {
            $school = School::first();
            $exam = Exam::create([
                'school_id' => $school?->id,
                'academic_session_id' => $validated['academic_session_id'],
                'exam_type_id' => $validated['exam_type_id'],
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'result_publish_date' => $validated['result_publish_date'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'description' => $validated['description'] ?? null,
            ]);

            $this->syncClassConfigs($exam, $validated['class_configs'] ?? [], $school?->id);
            $this->syncSubjectConfigs($exam, $validated['subject_configs'] ?? [], $school?->id);

            return $exam->load([
                'examClassConfigs.schoolClass', 'examClassConfigs.section',
                'examSubjectConfigs.subject', 'examSubjectConfigs.schoolClass', 'examSubjectConfigs.markComponents',
                'academicSession', 'examType',
            ]);
        });
    }

    public function updateExamWithConfigs(Exam $exam, array $validated): Exam
    {
        return DB::transaction(function () use ($exam, $validated) {
            $updates = ['name', 'code', 'start_date', 'end_date', 'result_publish_date', 'status', 'description'];
            foreach ($updates as $key) {
                if (array_key_exists($key, $validated)) {
                    $exam->setAttribute($key, $validated[$key]);
                }
            }
            $exam->save();

            if (isset($validated['class_configs'])) {
                $this->syncClassConfigs($exam, $validated['class_configs'], $exam->school_id);
            }
            if (isset($validated['subject_configs'])) {
                $this->syncSubjectConfigs($exam, $validated['subject_configs'], $exam->school_id);
            }

            return $exam->fresh([
                'examClassConfigs.schoolClass', 'examClassConfigs.section',
                'examSubjectConfigs.subject', 'examSubjectConfigs.schoolClass', 'examSubjectConfigs.markComponents',
                'academicSession', 'examType',
            ]);
        });
    }

    private function syncClassConfigs(Exam $exam, array $configs, ?int $schoolId): void
    {
        $existingIds = collect($configs)->pluck('id')->filter()->values()->all();
        $exam->examClassConfigs()->whereNotIn('id', $existingIds)->delete();

        foreach ($configs as $row) {
            $payload = [
                'school_id' => $schoolId,
                'exam_id' => $exam->id,
                'class_id' => $row['class_id'],
                'section_id' => $row['section_id'] ?? null,
                'status' => $row['status'] ?? 'active',
            ];
            if (! empty($row['id'])) {
                $config = ExamClassConfig::find($row['id']);
                if ($config && $config->exam_id === $exam->id) {
                    $config->update($payload);
                    continue;
                }
            }
            ExamClassConfig::create($payload);
        }
    }

    private function syncSubjectConfigs(Exam $exam, array $configs, ?int $schoolId): void
    {
        $existingIds = collect($configs)->pluck('id')->filter()->values()->all();
        $exam->examSubjectConfigs()->whereNotIn('id', $existingIds)->delete();

        foreach ($configs as $row) {
            $payload = [
                'school_id' => $schoolId,
                'exam_id' => $exam->id,
                'class_id' => $row['class_id'],
                'subject_id' => $row['subject_id'],
                'full_marks' => (int) $row['full_marks'],
                'pass_marks' => (int) $row['pass_marks'],
                'theory_marks' => isset($row['theory_marks']) ? (int) $row['theory_marks'] : null,
                'practical_marks' => isset($row['practical_marks']) ? (int) $row['practical_marks'] : null,
                'oral_marks' => isset($row['oral_marks']) ? (int) $row['oral_marks'] : null,
                'has_practical' => (bool) ($row['has_practical'] ?? false),
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : null,
            ];
            if (! empty($row['id'])) {
                $config = ExamSubjectConfig::find($row['id']);
                if ($config && $config->exam_id === $exam->id) {
                    $config->update($payload);
                    $this->syncMarkComponents($config, $row['mark_components'] ?? [], $schoolId);
                    continue;
                }
            }
            $subjectConfig = ExamSubjectConfig::create($payload);
            $this->syncMarkComponents($subjectConfig, $row['mark_components'] ?? [], $schoolId);
        }
    }

    private function syncMarkComponents(ExamSubjectConfig $subjectConfig, array $components, ?int $schoolId): void
    {
        $existingIds = collect($components)->pluck('id')->filter()->values()->all();
        $subjectConfig->markComponents()->whereNotIn('id', $existingIds)->delete();

        foreach ($components as $row) {
            $payload = [
                'school_id' => $schoolId,
                'exam_subject_config_id' => $subjectConfig->id,
                'name' => $row['name'],
                'component_type' => $row['component_type'] ?? 'theory',
                'marks' => (int) $row['marks'],
                'pass_marks' => isset($row['pass_marks']) ? (int) $row['pass_marks'] : null,
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : null,
            ];
            if (! empty($row['id'])) {
                $comp = MarkComponent::find($row['id']);
                if ($comp && $comp->exam_subject_config_id === $subjectConfig->id) {
                    $comp->update($payload);
                    continue;
                }
            }
            MarkComponent::create($payload);
        }
    }
}
