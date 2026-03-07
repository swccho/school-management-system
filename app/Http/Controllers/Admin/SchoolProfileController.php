<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSchoolProfileRequest;
use App\Http\Requests\Admin\UpdateSchoolSettingsRequest;
use App\Services\SchoolProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolProfileController extends Controller
{
    public function __construct(
        private SchoolProfileService $schoolProfileService
    ) {}

    public function show(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-settings')) {
            abort(403, 'Unauthorized.');
        }

        $school = $this->schoolProfileService->getDefaultSchool();
        if (! $school) {
            abort(404, 'School profile not found.');
        }

        $school->load('settings');

        return response()->json([
            'id' => $school->id,
            'name' => $school->name,
            'code' => $school->code,
            'email' => $school->email,
            'phone' => $school->phone,
            'website' => $school->website,
            'established_year' => $school->established_year,
            'principal_name' => $school->principal_name,
            'slogan' => $school->slogan,
            'short_name' => $school->short_name,
            'address' => $school->address,
            'city' => $school->city,
            'district' => $school->district,
            'country' => $school->country,
            'postal_code' => $school->postal_code,
            'description' => $school->description,
            'logo_path' => $school->logo_path,
            'favicon_path' => $school->favicon_path,
            'logo_url' => $school->logo_path ? Storage::url($school->logo_path) : null,
            'favicon_url' => $school->favicon_path ? Storage::url($school->favicon_path) : null,
            'status' => $school->status,
            'settings' => $school->settings ? [
                'default_language' => $school->settings->default_language,
                'timezone' => $school->settings->timezone,
                'date_format' => $school->settings->date_format,
                'time_format' => $school->settings->time_format,
                'default_currency' => $school->settings->default_currency,
                'attendance_mode' => $school->settings->attendance_mode,
                'result_publish_policy' => $school->settings->result_publish_policy,
                'theme' => $school->settings->theme,
                'maintenance_mode' => $school->settings->maintenance_mode,
            ] : null,
        ]);
    }

    public function updateProfile(UpdateSchoolProfileRequest $request): JsonResponse
    {
        $school = $this->schoolProfileService->getDefaultSchool();
        if (! $school) {
            abort(404, 'School profile not found.');
        }

        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo');
        }
        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon');
        }

        $school = $this->schoolProfileService->updateProfile($school, $data);

        return response()->json([
            'message' => 'School profile updated.',
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'code' => $school->code,
                'email' => $school->email,
                'phone' => $school->phone,
                'website' => $school->website,
                'established_year' => $school->established_year,
                'principal_name' => $school->principal_name,
                'slogan' => $school->slogan,
                'short_name' => $school->short_name,
                'address' => $school->address,
                'city' => $school->city,
                'district' => $school->district,
                'country' => $school->country,
                'postal_code' => $school->postal_code,
                'description' => $school->description,
                'logo_path' => $school->logo_path,
                'favicon_path' => $school->favicon_path,
                'logo_url' => $school->logo_path ? Storage::url($school->logo_path) : null,
                'favicon_url' => $school->favicon_path ? Storage::url($school->favicon_path) : null,
                'status' => $school->status,
            ],
        ]);
    }

    public function getSettings(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-settings')) {
            abort(403, 'Unauthorized.');
        }

        $school = $this->schoolProfileService->getDefaultSchool();
        if (! $school) {
            abort(404, 'School not found.');
        }

        $school->load('settings');
        $settings = $school->settings;

        if (! $settings) {
            return response()->json([
                'default_language' => null,
                'timezone' => null,
                'date_format' => null,
                'time_format' => null,
                'default_currency' => null,
                'attendance_mode' => null,
                'result_publish_policy' => null,
                'theme' => null,
                'maintenance_mode' => false,
            ]);
        }

        return response()->json([
            'default_language' => $settings->default_language,
            'timezone' => $settings->timezone,
            'date_format' => $settings->date_format,
            'time_format' => $settings->time_format,
            'default_currency' => $settings->default_currency,
            'attendance_mode' => $settings->attendance_mode,
            'result_publish_policy' => $settings->result_publish_policy,
            'theme' => $settings->theme,
            'maintenance_mode' => $settings->maintenance_mode,
        ]);
    }

    public function updateSettings(UpdateSchoolSettingsRequest $request): JsonResponse
    {
        $school = $this->schoolProfileService->getDefaultSchool();
        if (! $school) {
            abort(404, 'School not found.');
        }

        $this->schoolProfileService->updateSettings($school, $request->validated());
        $school->load('settings');
        $settings = $school->settings;

        return response()->json([
            'message' => 'System settings updated.',
            'settings' => $settings ? [
                'default_language' => $settings->default_language,
                'timezone' => $settings->timezone,
                'date_format' => $settings->date_format,
                'time_format' => $settings->time_format,
                'default_currency' => $settings->default_currency,
                'attendance_mode' => $settings->attendance_mode,
                'result_publish_policy' => $settings->result_publish_policy,
                'theme' => $settings->theme,
                'maintenance_mode' => $settings->maintenance_mode,
            ] : null,
        ]);
    }
}
