<?php

use App\Http\Controllers\Admin\AcademicSessionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SchoolProfileController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('roles/{role}', [RoleController::class, 'show']);
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::get('school-profile', [SchoolProfileController::class, 'show']);
        Route::post('school-profile', [SchoolProfileController::class, 'updateProfile']);
        Route::get('school-settings', [SchoolProfileController::class, 'getSettings']);
        Route::put('school-settings', [SchoolProfileController::class, 'updateSettings']);
        Route::get('academic-sessions', [AcademicSessionController::class, 'index']);
        Route::post('academic-sessions', [AcademicSessionController::class, 'store']);
        Route::get('academic-sessions/{academic_session}', [AcademicSessionController::class, 'show']);
        Route::put('academic-sessions/{academic_session}', [AcademicSessionController::class, 'update']);
        Route::post('academic-sessions/{academic_session}/set-current', [AcademicSessionController::class, 'setCurrent']);
        Route::get('classes', [SchoolClassController::class, 'index']);
        Route::post('classes', [SchoolClassController::class, 'store']);
        Route::get('classes/{school_class}', [SchoolClassController::class, 'show']);
        Route::put('classes/{school_class}', [SchoolClassController::class, 'update']);
        Route::get('sections', [SectionController::class, 'index']);
        Route::post('sections', [SectionController::class, 'store']);
        Route::get('sections/{section}', [SectionController::class, 'show']);
        Route::put('sections/{section}', [SectionController::class, 'update']);
        Route::get('subjects', [SubjectController::class, 'index']);
        Route::post('subjects', [SubjectController::class, 'store']);
        Route::get('subjects/{subject}', [SubjectController::class, 'show']);
        Route::put('subjects/{subject}', [SubjectController::class, 'update']);
    });
});
