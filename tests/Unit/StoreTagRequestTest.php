<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_tag_name_passes_validation(): void
    {
        $request = new StoreTagRequest;

        $data = [
            'name' => '新しいタグ',
        ];

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_duplicate_tag_name_fails_validation(): void
    {
        Tag::create([
            'name' => '質問',
        ]);

        $request = new StoreTagRequest;

        $data = [
            'name' => '質問',
        ];

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }
}
