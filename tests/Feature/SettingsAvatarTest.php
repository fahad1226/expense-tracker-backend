<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('settings includes null avatar url when user has no avatar', function () {
    $user = User::factory()->create(['avatar_path' => null]);
    Sanctum::actingAs($user);

    $this->getJson('/api/settings')
        ->assertSuccessful()
        ->assertJsonPath('user.avatar_url', null);
});

test('user can upload avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create(['avatar_path' => null]);
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->image('avatar.jpg', 320, 320);

    $this->post('/api/settings/avatar', [
        'avatar' => $file,
    ], [
        'Accept' => 'application/json',
    ])->assertSuccessful()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'currency', 'avatar_url'],
        ]);

    $user->refresh();
    expect($user->avatar_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($user->avatar_path))->toBeTrue();
});

test('replacing avatar deletes previous file', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $one = UploadedFile::fake()->image('one.jpg');
    $this->post('/api/settings/avatar', ['avatar' => $one], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $user->refresh();
    $firstPath = $user->avatar_path;
    expect($firstPath)->not->toBeNull();

    $two = UploadedFile::fake()->image('two.jpg');
    $this->post('/api/settings/avatar', ['avatar' => $two], ['Accept' => 'application/json'])
        ->assertSuccessful();

    expect(Storage::disk('public')->exists($firstPath))->toBeFalse();
    $user->refresh();
    expect($user->avatar_path)->not->toBe($firstPath);
});

test('user can remove avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->image('a.jpg');
    $this->post('/api/settings/avatar', ['avatar' => $file], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $user->refresh();
    $path = $user->avatar_path;
    expect($path)->not->toBeNull();

    $this->deleteJson('/api/settings/avatar')
        ->assertSuccessful()
        ->assertJsonPath('user.avatar_url', null);

    $user->refresh();
    expect($user->avatar_path)->toBeNull()
        ->and(Storage::disk('public')->exists((string) $path))->toBeFalse();
});
