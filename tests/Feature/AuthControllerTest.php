<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── Registration ────────────────────────────────────────────

    public function test_register_successfully(): void
    {
        $response = $this->postJson(route('auth.register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user' => ['name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_register_with_invalid_email(): void
    {
        $this->postJson(route('auth.register'), [
            'name' => 'John',
            'email' => 'not-an-email',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertStatus(422);
    }

    public function test_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson(route('auth.register'), [
            'name' => 'Jane',
            'email' => 'taken@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertStatus(422);
    }

    public function test_register_with_mismatched_passwords(): void
    {
        $this->postJson(route('auth.register'), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ])->assertStatus(422);
    }

    // ─── Login ───────────────────────────────────────────────────

    public function test_login_successfully(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $this->postJson(route('auth.login'), [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ])->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_login_with_missing_fields(): void
    {
        $this->postJson(route('auth.login'), [])
            ->assertStatus(422);
    }

    // ─── Logout ──────────────────────────────────────────────────

    public function test_logout_successfully(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson(route('auth.logout'))
            ->assertStatus(200)
            ->assertJsonPath('message', 'Logged out successfully.');
    }

    // ─── Password Recovery ───────────────────────────────────────

    public function test_forgot_password_sends_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'john@example.com']);

        $this->postJson(route('auth.password.forgot'), ['email' => 'john@example.com'])
            ->assertStatus(200);

        Notification::assertSentTo(
            $user,
            \App\Notifications\ResetPasswordNotification::class
        );
    }

    public function test_forgot_password_with_unknown_email(): void
    {
        $this->postJson(route('auth.password.forgot'), ['email' => 'ghost@example.com'])
            ->assertStatus(404)
            ->assertJsonPath('message', 'User not found.');
    }

    public function test_reset_password_successfully(): void
    {
        $user = User::factory()->create(['email' => 'john@example.com']);
        $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);

        $this->postJson(route('auth.password.reset'), [
            'token' => $token,
            'email' => 'john@example.com',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertStatus(200)
            ->assertJsonPath('message', 'Password reset successfully.');
    }

    public function test_reset_password_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $this->postJson(route('auth.password.reset'), [
            'token' => 'invalid-token',
            'email' => 'john@example.com',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Invalid or expired token.');
    }

    public function test_reset_password_with_missing_fields(): void
    {
        $this->postJson(route('auth.password.reset'), [])
            ->assertStatus(422);
    }
}
