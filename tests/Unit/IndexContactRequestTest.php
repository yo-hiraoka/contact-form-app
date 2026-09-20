<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    public function test_valid_search_parameters_pass_validation(): void
    {
        $request = new IndexContactRequest;

        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'date' => '2026-09-20',
        ];

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_gender_fails_validation(): void
    {
        $request = new IndexContactRequest;

        $data = [
            'gender' => 4,
        ];

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }
}
