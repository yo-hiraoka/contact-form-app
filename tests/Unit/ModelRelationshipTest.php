<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_contacts(): void
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
            'address' => '東京都渋谷区',
            'building' => null,
            'detail' => '商品について質問があります。',
        ]);

        $this->assertCount(1, $category->contacts);
        $this->assertInstanceOf(
            Contact::class,
            $category->contacts->first()
        );
    }

    public function test_contact_belongs_to_category(): void
    {
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
            'detail' => '商品について質問があります。',
        ]);

        $this->assertInstanceOf(
            Category::class,
            $contact->category
        );

        $this->assertEquals(
            $category->id,
            $contact->category->id
        );
    }

    public function test_contact_belongs_to_many_tags(): void
    {
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
            'detail' => '商品について質問があります。',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $contact->tags()->attach($tag->id);

        $this->assertCount(1, $contact->tags);

        $this->assertEquals(
            $tag->id,
            $contact->tags->first()->id
        );
    }

    public function test_tag_belongs_to_many_contacts(): void
    {
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
            'detail' => '商品について質問があります。',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $tag->contacts()->attach($contact->id);

        $this->assertCount(1, $tag->contacts);

        $this->assertEquals(
            $contact->id,
            $tag->contacts->first()->id
        );
    }
}
