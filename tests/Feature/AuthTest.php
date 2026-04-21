<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_receive_token()
    {
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'admin' => ['id','name','email'], 'token']);
    }

    public function test_admin_can_logout_and_token_is_revoked()
    {
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password123')
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }
}
