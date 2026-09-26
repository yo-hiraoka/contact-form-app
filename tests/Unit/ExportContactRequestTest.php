<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    public function test_invalid_gender_fails_validation(): void
    {
        $request = new ExportContactRequest;

        $validator = Validator::make(
            ['gender' => 4],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    public function test_nonexistent_category_id_fails_validation(): void
    {
        $request = new ExportContactRequest;

        $validator = Validator::make(
            ['category_id' => 999999],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    public function test_valid_export_filters_pass_validation(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $request = new ExportContactRequest;

        $validator = Validator::make(
            [
                'keyword' => '山田',
                'gender' => 1,
                'category_id' => $category->id,
                'date' => '2026-09-26',
            ],
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }
}
