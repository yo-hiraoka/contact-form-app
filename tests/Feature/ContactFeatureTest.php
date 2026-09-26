<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_index_is_displayed(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    public function test_contact_index_receives_form_data_from_query_parameters(): void
    {
        $formData = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1',
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101',
            'category_id' => '1',
            'tag_ids' => ['1'],
            'detail' => '商品について質問があります。',
        ];

        $response = $this->get('/?'.http_build_query($formData));

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas('formData', $formData);
        $response->assertSee('name="last_name" placeholder="例: 山田" value="山田"', false);
    }

    public function test_thanks_page_is_displayed(): void
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);
        $response->assertViewIs('contact.thanks');
    }

    public function test_confirm_page_is_displayed_with_valid_data(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => '商品について質問があります。',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertViewHas('validated');
        $response->assertViewHas('category');
        $response->assertViewHas('tags');
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('質問');
        $response->assertSee('<form action="/" method="get">', false);
        $response->assertSee('name="last_name" value="山田"', false);
        $response->assertSee('name="tel" value="09012345678"', false);
        $response->assertSee('name="tag_ids[]" value="'.$tag->id.'"', false);
    }

    public function test_confirm_fails_with_invalid_data(): void
    {
        $response = $this
            ->from('/')
            ->post('/contacts/confirm', []);

        $response->assertRedirect('/');

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    public function test_contact_is_stored_with_tags(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => '商品について質問があります。',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->post('/contacts', $data);

        $response->assertRedirect('/thanks');

        $this->assertDatabaseHas('contacts', [
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
            'tel' => '09012345678',
        ]);

        $contact = Contact::where(
            'email',
            'taro@example.com'
        )->firstOrFail();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }
}
