<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SchoolProfileService
{
    public function getDefaultSchool(): ?School
    {
        return School::with('settings')->first();
    }

    public function updateProfile(School $school, array $data): School
    {
        $fillable = [
            'name', 'code', 'email', 'phone', 'website', 'established_year',
            'principal_name', 'slogan', 'short_name', 'address', 'city', 'district',
            'country', 'postal_code', 'description', 'status',
        ];

        $school->update(collect($data)->only($fillable)->all());

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $this->storeLogo($school, $data['logo']);
        }
        if (isset($data['favicon']) && $data['favicon'] instanceof UploadedFile) {
            $this->storeFavicon($school, $data['favicon']);
        }

        return $school->fresh();
    }

    public function updateSettings(School $school, array $data): void
    {
        $settings = $school->settings ?? $school->settings()->create(['school_id' => $school->id]);
        $settings->update(collect($data)->only([
            'default_language', 'timezone', 'date_format', 'time_format',
            'default_currency', 'attendance_mode', 'result_publish_policy',
            'theme', 'maintenance_mode',
        ])->all());
    }

    private function storeLogo(School $school, UploadedFile $file): void
    {
        if ($school->logo_path) {
            Storage::disk('public')->delete($school->logo_path);
        }
        $path = $file->store('school', 'public');
        $school->update(['logo_path' => $path]);
    }

    private function storeFavicon(School $school, UploadedFile $file): void
    {
        if ($school->favicon_path) {
            Storage::disk('public')->delete($school->favicon_path);
        }
        $path = $file->store('school', 'public');
        $school->update(['favicon_path' => $path]);
    }
}
