<?php

namespace Tests\Feature\Admin;

use App\Models\Notice;
use App\Models\NoticeCategory;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class NoticeTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_returns_403_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/notices');

        $response->assertStatus(403);
    }

    public function test_index_returns_notices_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-notices');
        $this->createSchool();
        $category = NoticeCategory::create(['name' => 'General', 'slug' => 'general', 'status' => 'active']);
        Notice::create([
            'school_id' => null,
            'title' => 'Test Notice',
            'slug' => 'test-notice',
            'content' => 'Content',
            'category_id' => $category->id,
            'publish_date' => now(),
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/admin/notices');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_store_creates_notice_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission('create-notices');
        $this->createSchool();
        $category = NoticeCategory::create(['name' => 'Announcement', 'slug' => 'announcement', 'status' => 'active']);

        $response = $this->postJson('/api/admin/notices', [
            'title' => 'New Notice',
            'content' => 'Notice content here.',
            'category_id' => $category->id,
            'publish_date' => now()->toDateString(),
            'status' => 'draft',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Notice created.',
                'notice' => [
                    'title' => 'New Notice',
                ],
            ]);

        $this->assertDatabaseHas('notices', ['title' => 'New Notice']);
    }

    public function test_show_returns_notice(): void
    {
        $this->actingAsAdminWithPermission('view-notices');
        $category = NoticeCategory::create(['name' => 'General', 'slug' => 'general-2', 'status' => 'active']);
        $notice = Notice::create([
            'school_id' => null,
            'title' => 'Show Notice',
            'slug' => 'show-notice',
            'content' => 'Content',
            'category_id' => $category->id,
            'publish_date' => now(),
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/admin/notices/'.$notice->id);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Show Notice']);
    }
}
