<?php

namespace Tests\Unit\Services;

use App\Models\AcademicSession;
use App\Models\Student;
use App\Services\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_dashboard_data_returns_all_required_keys(): void
    {
        $service = new AdminDashboardService;
        $data = $service->getDashboardData();

        $this->assertArrayHasKey('summary_cards', $data);
        $this->assertArrayHasKey('academic_overview', $data);
        $this->assertArrayHasKey('attendance_overview', $data);
        $this->assertArrayHasKey('exam_overview', $data);
        $this->assertArrayHasKey('recent_activity', $data);

        $this->assertArrayHasKey('total_students', $data['summary_cards']);
        $this->assertArrayHasKey('total_teachers', $data['summary_cards']);
        $this->assertArrayHasKey('total_staff', $data['summary_cards']);
        $this->assertArrayHasKey('total_classes', $data['summary_cards']);
        $this->assertArrayHasKey('total_exams', $data['summary_cards']);
        $this->assertArrayHasKey('published_notices_count', $data['summary_cards']);

        $this->assertArrayHasKey('present', $data['attendance_overview']);
        $this->assertArrayHasKey('absent', $data['attendance_overview']);
        $this->assertArrayHasKey('total_marked', $data['attendance_overview']);

        $this->assertArrayHasKey('latest_students', $data['recent_activity']);
        $this->assertArrayHasKey('latest_notices', $data['recent_activity']);
        $this->assertArrayHasKey('latest_exams', $data['recent_activity']);
        $this->assertArrayHasKey('latest_activity_logs', $data['recent_activity']);
    }

    public function test_get_dashboard_data_with_empty_database_returns_zero_counts(): void
    {
        $service = new AdminDashboardService;
        $data = $service->getDashboardData();

        $this->assertSame(0, $data['summary_cards']['total_students']);
        $this->assertSame(0, $data['summary_cards']['total_teachers']);
        $this->assertSame(0, $data['summary_cards']['total_staff']);
        $this->assertSame(0, $data['summary_cards']['total_classes']);
        $this->assertSame(0, $data['summary_cards']['total_exams']);
        $this->assertSame(0, $data['summary_cards']['published_notices_count']);
        $this->assertSame(0, $data['attendance_overview']['total_marked']);
        $this->assertNull($data['academic_overview']['current_session']);
        $this->assertSame([], $data['recent_activity']['latest_students']);
    }

    public function test_get_dashboard_data_reflects_student_count_and_recent_students(): void
    {
        $school = \App\Models\School::factory()->create();
        Student::factory()->forSchool($school)->count(3)->create();

        $service = new AdminDashboardService;
        $data = $service->getDashboardData();

        $this->assertSame(3, $data['summary_cards']['total_students']);
        $this->assertCount(3, $data['recent_activity']['latest_students']);
    }

    public function test_get_dashboard_data_includes_current_session_when_set(): void
    {
        $school = \App\Models\School::factory()->create();
        AcademicSession::factory()->forSchool($school)->current()->create([
            'name' => '2024-25',
            'code' => '2425',
        ]);

        $service = new AdminDashboardService;
        $data = $service->getDashboardData();

        $this->assertNotNull($data['academic_overview']['current_session']);
        $this->assertSame('2024-25', $data['academic_overview']['current_session']['name']);
        $this->assertSame('2425', $data['academic_overview']['current_session']['code']);
    }
}
