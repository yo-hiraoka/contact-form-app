<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_tag_management(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        $this->post('/admin/tags', [
            'name' => '新しいタグ',
        ])->assertRedirect('/login');

        $this->get('/admin/tags/'.$tag->id.'/edit')
            ->assertRedirect('/login');

        $this->put('/admin/tags/'.$tag->id, [
            'name' => '更新したタグ',
        ])->assertRedirect('/login');

        $this->delete('/admin/tags/'.$tag->id)
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_tag(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/admin/tags', [
                'name' => '新しいタグ',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'name' => '新しいタグ',
        ]);
    }

    public function test_authenticated_user_can_view_tag_edit_page(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/tags/'.$tag->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');
        $response->assertViewHas('tag');
        $response->assertSee('質問');
    }

    public function test_authenticated_user_can_update_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $response = $this
            ->actingAs($user)
            ->put('/admin/tags/'.$tag->id, [
                'name' => '更新したタグ',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新したタグ',
        ]);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
            'name' => '質問',
        ]);
    }

    public function test_authenticated_user_can_delete_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete('/admin/tags/'.$tag->id);

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }
}
