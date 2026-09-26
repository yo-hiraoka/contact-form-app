<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_can_be_listed(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag1 = Tag::create([
            'name' => '質問',
        ]);

        $tag2 = Tag::create([
            'name' => '要望',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'building' => 'テストマンション101号',
            'detail' => 'テストのお問い合わせです。',
        ]);

        $contact->tags()->attach([$tag1->id, $tag2->id]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk()
            ->assertJsonPath('data.0.first_name', '太郎')
            ->assertJsonPath('data.0.last_name', '山田')
            ->assertJsonPath('data.0.category.id', $category->id)
            ->assertJsonCount(2, 'data.0.tags');
    }

    public function test_contacts_can_be_filtered_by_keyword(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'detail' => 'お問い合わせ1',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '静岡県静岡市',
            'detail' => 'お問い合わせ2',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=山田');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', '太郎')
            ->assertJsonPath('data.0.last_name', '山田');
    }

    public function test_contacts_can_be_filtered_by_gender(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'detail' => 'お問い合わせ1',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '静岡県静岡市',
            'detail' => 'お問い合わせ2',
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=2');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', '花子')
            ->assertJsonPath('data.0.gender', 2);
    }

    public function test_contacts_can_be_filtered_by_category(): void
    {
        $category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $category2 = Category::create([
            'content' => '商品の交換について',
        ]);

        Contact::create([
            'category_id' => $category1->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'detail' => 'お問い合わせ1',
        ]);

        Contact::create([
            'category_id' => $category2->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '静岡県静岡市',
            'detail' => 'お問い合わせ2',
        ]);

        $response = $this->getJson(
            "/api/v1/contacts?category_id={$category2->id}"
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', '花子')
            ->assertJsonPath('data.0.category.id', $category2->id);
    }

    public function test_contacts_can_be_paginated_with_per_page(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        for ($i = 1; $i <= 5; $i++) {
            Contact::create([
                'category_id' => $category->id,
                'first_name' => "太郎{$i}",
                'last_name' => '山田',
                'gender' => 1,
                'email' => "taro{$i}@example.com",
                'tel' => '09012345678',
                'address' => '静岡県浜松市',
                'detail' => "お問い合わせ{$i}",
            ]);
        }

        $response = $this->getJson('/api/v1/contacts?per_page=3');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('links.next', function ($url) {
                return str_contains($url, 'per_page=3');
            });
    }

    public function test_contact_can_be_shown(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'detail' => '詳細取得テストです。',
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonPath('data.first_name', '太郎')
            ->assertJsonPath('data.last_name', '山田')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.tags.0.id', $tag->id);
    }

    public function test_contact_can_be_created(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag1 = Tag::create([
            'name' => '質問',
        ]);

        $tag2 = Tag::create([
            'name' => '要望',
        ]);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'building' => 'テストマンション101号',
            'category_id' => $category->id,
            'detail' => 'API新規登録テストです。',
            'tag_ids' => [$tag1->id, $tag2->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.first_name', '太郎')
            ->assertJsonPath('data.last_name', '山田')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonCount(2, 'data.tags');

        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
            'category_id' => $category->id,
        ]);

        $contact = Contact::where('email', 'taro@example.com')->firstOrFail();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag2->id,
        ]);
    }

    public function test_contact_creation_validation_fails_with_invalid_data(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 0,
            'email' => 'taro@example.com',
            'tel' => '123',
            'address' => '静岡県浜松市',
            'category_id' => $category->id,
            'detail' => 'バリデーションテストです。',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'gender',
                'tel',
            ]);

        $this->assertDatabaseMissing('contacts', [
            'email' => 'taro@example.com',
        ]);
    }

    public function test_contact_can_be_updated(): void
    {
        $category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $category2 = Category::create([
            'content' => '商品の交換について',
        ]);

        $tag1 = Tag::create([
            'name' => '質問',
        ]);

        $tag2 = Tag::create([
            'name' => '要望',
        ]);

        $contact = Contact::create([
            'category_id' => $category1->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'detail' => '更新前のお問い合わせです。',
        ]);

        $contact->tags()->attach($tag1->id);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '静岡県静岡市',
            'building' => 'テストビル202号',
            'category_id' => $category2->id,
            'detail' => '更新後のお問い合わせです。',
            'tag_ids' => [$tag2->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.first_name', '花子')
            ->assertJsonPath('data.last_name', '佐藤')
            ->assertJsonPath('data.category.id', $category2->id)
            ->assertJsonCount(1, 'data.tags')
            ->assertJsonPath('data.tags.0.id', $tag2->id);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'category_id' => $category2->id,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag2->id,
        ]);
    }

    public function test_contact_can_be_deleted(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'detail' => '削除テストです。',
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_contact_index_validation_fails_with_invalid_parameters(): void
    {
        $response = $this->getJson(
            '/api/v1/contacts?gender=0&per_page=101'
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'gender',
                'per_page',
            ]);
    }

    public function test_contacts_can_be_filtered_by_date(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $oldContact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '静岡県浜松市',
            'detail' => '古いお問い合わせです。',
        ]);

        $newContact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '静岡県静岡市',
            'detail' => '新しいお問い合わせです。',
        ]);

        Contact::whereKey($oldContact->id)->update([
            'created_at' => '2026-09-21 10:00:00',
        ]);

        Contact::whereKey($newContact->id)->update([
            'created_at' => '2026-09-22 10:00:00',
        ]);

        $response = $this->getJson('/api/v1/contacts?date=2026-09-22');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', '花子')
            ->assertJsonPath('data.0.last_name', '佐藤');
    }

    public function test_contact_show_returns_custom_404_when_contact_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/contacts/999999');

        $response->assertNotFound()
            ->assertExactJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }

    public function test_contact_update_returns_custom_404_when_contact_does_not_exist(): void
    {
        $response = $this->putJson('/api/v1/contacts/999999', []);

        $response->assertNotFound()
            ->assertExactJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }

    public function test_contact_destroy_returns_custom_404_when_contact_does_not_exist(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/999999');

        $response->assertNotFound()
            ->assertExactJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }
}
