<?php

namespace Tests\Unit;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateNewUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_password_fails_with_correct_message(): void
    {
        $action = new CreateNewUser;

        try {
            $action->create([
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'pass123',
                'password_confirmation' => 'pass123',
            ]);

            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                'パスワードは 8文字以上で入力してください',
                $e->errors()['password'][0]
            );
        }
    }

    public function test_password_confirmation_mismatch_fails_with_correct_message(): void
    {
        $action = new CreateNewUser;

        try {
            $action->create([
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'different',
            ]);

            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                'パスワードと一致しません',
                $e->errors()['password'][0]
            );
        }
    }
}
