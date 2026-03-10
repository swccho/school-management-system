<?php

namespace Tests\Feature\Admin;

use App\Models\Student;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use ActsAsAdmin;

    public function test_dashboard_returns_correct_structure(): void
    {
        $this->actingAsAdminWithPermission('view-dashboard');

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('summary_cards', $data);
        $this->assertArrayHasKey('academic_overview', $data);
        $this->assertArrayHasKey('attendance_overview', $data);
        $this->assertArrayHasKey('exam_overview', $data);
        $this->assertArrayHasKey('recent_activity', $data);

        $this->assertIsArray($data['summary_cards']);
        $this->assertArrayHasKey('total_students', $data['summary_cards']);
        $this->assertArrayHasKey('total_teachers', $data['summary_cards']);
        $this->assertArrayHasKey('total_staff', $data['summary_cards']);
        $this->assertArrayHasKey('total_classes', $data['summary_cards']);
        $this->assertArrayHasKey('total_exams', $data['summary_cards']);
        $this->assertArrayHasKey('published_notices_count', $data['summary_cards']);

        $this->assertIsInt($data['summary_cards']['total_students']);
        $this->assertIsInt($data['summary_cards']['total_teachers']);

        $this->assertArrayHasKey('present', $data['attendance_overview']);
        $this->assertArrayHasKey('absent', $data['attendance_overview']);
        $this->assertArrayHasKey('total_marked', $data['attendance_overview']);

        $this->assertArrayHasKey('latest_students', $data['recent_activity']);
        $this->assertArrayHasKey('latest_notices', $data['recent_activity']);
        $this->assertArrayHasKey('latest_exams', $data['recent_activity']);
        $this->assertArrayHasKey('latest_activity_logs', $data['recent_activity']);
    }

    public function test_dashboard_summary_cards_reflect_student_count(): void
    {
        $this->actingAsAdminWithPermission('view-dashboard');

        $school = $this->createSchool();
        Student::factory()->forSchool($school)->count(3)->create();

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        $this->assertSame(3, $response->json('summary_cards.total_students'));
    }
}
