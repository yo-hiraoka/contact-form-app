<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_admin(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('contacts');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
    }

    public function test_admin_can_search_contacts_by_keyword(): void
    {
        $user = User::factory()->create();

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
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => 'テストお問い合わせ1',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => null,
            'detail' => 'テストお問い合わせ2',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?keyword=山田');

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_admin_can_filter_contacts_by_gender(): void
    {
        $user = User::factory()->create();

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
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => '男性のお問い合わせ',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => null,
            'detail' => '女性のお問い合わせ',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?gender=1');

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_admin_can_filter_contacts_by_category(): void
    {
        $user = User::factory()->create();

        $category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $category2 = Category::create([
            'content' => '商品トラブル',
        ]);

        Contact::create([
            'category_id' => $category1->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => '配送についてのお問い合わせ',
        ]);

        Contact::create([
            'category_id' => $category2->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => null,
            'detail' => '商品トラブルについてのお問い合わせ',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?category_id='.$category1->id);

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_admin_can_filter_contacts_by_date(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact1 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => '9月20日のお問い合わせ',
        ]);

        $contact1->timestamps = false;
        $contact1->created_at = '2026-09-20 10:00:00';
        $contact1->updated_at = '2026-09-20 10:00:00';
        $contact1->save();

        $contact2 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => null,
            'detail' => '9月19日のお問い合わせ',
        ]);

        $contact2->timestamps = false;
        $contact2->created_at = '2026-09-19 10:00:00';
        $contact2->updated_at = '2026-09-19 10:00:00';
        $contact2->save();

        $response = $this
            ->actingAs($user)
            ->get('/admin?date=2026-09-20');

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_admin_displays_seven_contacts_per_page(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        for ($i = 1; $i <= 8; $i++) {
            Contact::create([
                'category_id' => $category->id,
                'first_name' => "太郎{$i}",
                'last_name' => '山田',
                'gender' => 1,
                'email' => "taro{$i}@example.com",
                'tel' => '09012345678',
                'address' => '東京都渋谷区',
                'building' => null,
                'detail' => "テスト{$i}",
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);

        $contacts = $response->viewData('contacts');

        $this->assertCount(7, $contacts);
        $this->assertEquals(8, $contacts->total());
    }

    public function test_authenticated_user_can_view_contact_detail(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101',
            'detail' => 'お問い合わせ詳細のテストです',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/contacts/'.$contact->id);

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertViewHas('contact');
        $response->assertSee('山田');
        $response->assertSee('taro@example.com');
        $response->assertSee('商品のお届けについて');
    }

    public function test_authenticated_user_can_delete_contact(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => '削除テストのお問い合わせ',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete('/admin/contacts/'.$contact->id);

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_authenticated_user_can_export_contacts_as_csv(): void
    {
        $user = User::factory()->create();

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
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => 'CSVテストのお問い合わせ',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);
        $response->assertDownload('contacts.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString(
            'ID,氏名,性別,メール,電話,住所,建物,カテゴリ,内容,作成日時',
            $content
        );
        $this->assertStringContainsString('山田 太郎', $content);
        $this->assertStringContainsString('taro@example.com', $content);
        $this->assertStringNotContainsString('タグ', $content);
    }

    public function test_csv_export_respects_gender_filter(): void
    {
        $user = User::factory()->create();

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
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => '男性のお問い合わせ',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '09087654321',
            'address' => '東京都新宿区',
            'building' => null,
            'detail' => '女性のお問い合わせ',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export?gender=2');

        $response->assertStatus(200);
        $response->assertDownload('contacts.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString('佐藤 花子', $content);
        $this->assertStringContainsString('hanako@example.com', $content);
        $this->assertStringNotContainsString('山田 太郎', $content);
        $this->assertStringNotContainsString('taro@example.com', $content);
    }

    public function test_csv_export_without_filters_returns_all_contacts_in_latest_order(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'old@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => '古いお問い合わせ',
            'created_at' => '2026-09-01 10:00:00',
            'updated_at' => '2026-09-01 10:00:00',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'new@example.com',
            'tel' => '09087654321',
            'address' => '東京都新宿区',
            'building' => null,
            'detail' => '新しいお問い合わせ',
            'created_at' => '2026-09-02 10:00:00',
            'updated_at' => '2026-09-02 10:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);
        $response->assertDownload('contacts.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString('old@example.com', $content);
        $this->assertStringContainsString('new@example.com', $content);

        $newPosition = strpos($content, 'new@example.com');
        $oldPosition = strpos($content, 'old@example.com');

        $this->assertLessThan($newPosition, $oldPosition);
    }
}
