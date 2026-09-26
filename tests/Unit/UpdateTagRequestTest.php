<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_tag_name_passes_validation(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter($key, $default = null)
                {
                    return $key === 'tag' ? $this->tag : $default;
                }
            };
        });

        $data = [
            'name' => '質問',
        ];

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_duplicate_other_tag_name_fails_validation(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        Tag::create([
            'name' => '要望',
        ]);

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter($key, $default = null)
                {
                    return $key === 'tag' ? $this->tag : $default;
                }
            };
        });

        $data = [
            'name' => '要望',
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
        $this->assertSame(
            'そのタグ名は既に使用されています',
            $validator->errors()->first('name')
        );
    }

    public function test_too_long_tag_name_fails_with_correct_message(): void
    {
        $request = new UpdateTagRequest;

        $data = [
            'name' => str_repeat('あ', 51),
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'タグ名は 50文字以内で入力してください',
            $validator->errors()->first('name')
        );
    }
}
