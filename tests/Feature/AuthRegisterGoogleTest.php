<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('register returns token user and summary', function (): void {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password-plain',
        'password_confirmation' => 'password-plain',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.email', 'jane@example.com')
        ->assertJsonPath('user.currency', 'BDT')
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'currency']]);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'name' => 'Jane Doe',
    ]);
});

test('register requires password confirmation', function (): void {
    $this->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password-plain',
    ])->assertUnprocessable();
});

test('google auth creates user and returns token', function (): void {
    config(['services.google.client_id' => 'test-google-client-id']);

    Http::fake([
        'oauth2.googleapis.com/tokeninfo*' => Http::response([
            'aud' => 'test-google-client-id',
            'iss' => 'https://accounts.google.com',
            'email' => 'google-user@example.com',
            'email_verified' => 'true',
            'sub' => 'google-subject-999',
            'name' => 'Google User',
        ], 200),
    ]);

    $response = $this->postJson('/api/auth/google', [
        'credential' => 'fake-jwt-payload',
    ]);

    $response->assertOk()
        ->assertJsonPath('user.email', 'google-user@example.com')
        ->assertJsonStructure(['token', 'user']);

    $this->assertDatabaseHas('users', [
        'email' => 'google-user@example.com',
        'google_id' => 'google-subject-999',
    ]);
});

test('google auth rejects when not configured', function (): void {
    config(['services.google.client_id' => '']);

    $this->postJson('/api/auth/google', ['credential' => 'x'])
        ->assertStatus(503);
});
