<?php

namespace App\Services;

use App\Models\ClassRoutine;
use App\Models\ClassRoutineItem;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class ClassRoutineService
{
    public function createRoutineWithItems(array $validated): ClassRoutine
    {
        return DB::transaction(function () use ($validated) {
            $school = School::first();
            $routine = ClassRoutine::create([
                'school_id' => $school?->id,
                'academic_session_id' => $validated['academic_session_id'],
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
                'title' => $validated['title'] ?? null,
                'effective_from' => $validated['effective_from'],
                'effective_to' => $validated['effective_to'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->syncItems($routine, $validated['items'] ?? [], $school?->id);

            return $routine->load(['classRoutineItems.subject', 'classRoutineItems.teacher.staff', 'academicSession', 'schoolClass', 'section']);
        });
    }

    public function updateRoutineWithItems(ClassRoutine $routine, array $validated): ClassRoutine
    {
        return DB::transaction(function () use ($routine, $validated) {
            if (array_key_exists('title', $validated)) {
                $routine->title = $validated['title'];
            }
            if (array_key_exists('effective_from', $validated)) {
                $routine->effective_from = $validated['effective_from'];
            }
            if (array_key_exists('effective_to', $validated)) {
                $routine->effective_to = $validated['effective_to'];
            }
            if (array_key_exists('status', $validated)) {
                $routine->status = $validated['status'];
            }
            if (array_key_exists('remarks', $validated)) {
                $routine->remarks = $validated['remarks'];
            }
            $routine->save();

            if (isset($validated['items'])) {
                $this->syncItems($routine, $validated['items'], $routine->school_id);
            }

            return $routine->fresh(['classRoutineItems.subject', 'classRoutineItems.teacher.staff', 'academicSession', 'schoolClass', 'section']);
        });
    }

    private function syncItems(ClassRoutine $routine, array $items, ?int $schoolId): void
    {
        $existingIds = collect($items)->pluck('id')->filter()->values()->all();
        $routine->classRoutineItems()->whereNotIn('id', $existingIds)->delete();

        foreach ($items as $row) {
            $payload = [
                'school_id' => $schoolId,
                'class_routine_id' => $routine->id,
                'day_of_week' => $row['day_of_week'],
                'period_no' => (int) $row['period_no'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'subject_id' => $row['subject_id'],
                'teacher_id' => $row['teacher_id'] ?? null,
                'room_label' => $row['room_label'] ?? null,
                'remarks' => $row['remarks'] ?? null,
            ];
            if (! empty($row['id'])) {
                $item = ClassRoutineItem::find($row['id']);
                if ($item && $item->class_routine_id === $routine->id) {
                    $item->update($payload);
                    continue;
                }
            }
            ClassRoutineItem::create($payload);
        }
    }
}
