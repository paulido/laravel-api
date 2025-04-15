<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register()
    {
        $response = $this->postJson('/api/register', [
            'username' => 'john_doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['status', 'message', 'data' => ['user' => ['id', 'username', 'email']]]);
    }

    public function test_login()
    {
        $user = User::create([
            'username' => 'john_doe',
            'email' => 'john2@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john2@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'data' => ['token', 'user']]);
    }

    public function test_logout()
    {
        // Step 1: Create a user
        $user = User::factory()->create();

        // Step 2: Log in as this user using Sanctum
        Sanctum::actingAs($user);

        // Step 3: Make a GET request to the logout route
        $response = $this->getJson('/logout');

        // Step 4: Assert the response status or message
        $response->assertStatus(200)
            ->assertJson(['message' => __('logged_out')]);

        // Optionally, you can also check that the token was revoked if that's part of the logout process.
    }

    public function test_send_reset_link_email()
    {
        $user = User::create([
            'email' => 'john4@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/password/email', [
            'email' => 'john4@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => __('reset_link_sent')]);
    }

    public function test_reset_password()
    {
        $user = User::create([
            'email' => 'john5@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = Password::createToken($user);
        $response = $this->postJson('/api/password/reset', [
            'email' => 'john5@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => __('password_reset_successful')]);
    }

    public function test_authorization()
    {
        $user = User::factory()->create();
        $token = $user->createToken('token-name')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/authorization', [
                'roles' => ['user']
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['authorized']]);
    }

    public function test_verify_email()
    {
        $user = User::create([
            'email' => 'john6@example.com',
            'password' => Hash::make('password'),
        ]);

        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $response = $this->postJson($verificationUrl);

        $response->assertStatus(200)
            ->assertJson(['message' => __('email_verified')]);
    }
}
