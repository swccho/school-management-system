<?php

namespace App\Services;

use App\Models\AcademicSession;
use Illuminate\Support\Facades\DB;

class AcademicSessionService
{
    public function setCurrent(AcademicSession $session): void
    {
        DB::transaction(function () use ($session) {
            AcademicSession::query()
                ->where('id', '!=', $session->id)
                ->when($session->school_id !== null, fn ($q) => $q->where('school_id', $session->school_id))
                ->when($session->school_id === null, fn ($q) => $q->whereNull('school_id'))
                ->update(['is_current' => false]);

            $session->update(['is_current' => true]);
        });
    }
}
