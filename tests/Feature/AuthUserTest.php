<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('auth user rejects unauthenticated requests', function (): void {
    $this->getJson('/api/auth/user')->assertUnauthorized();
});

test('auth user returns summary for authenticated user', function (): void {
    $user = User::factory()->create([
        'name' => 'Auth User',
        'email' => 'auth-check@example.com',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/auth/user')
        ->assertSuccessful()
        ->assertJsonPath('user.email', 'auth-check@example.com')
        ->assertJsonStructure(['user' => ['id', 'name', 'email', 'currency', 'avatar_url']]);
});
