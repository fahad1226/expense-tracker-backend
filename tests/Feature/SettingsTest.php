<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('settings show requires authentication', function () {
    $this->getJson('/api/settings')->assertUnauthorized();
});

test('settings show returns user and currencies', function () {
    $user = User::factory()->create(['currency' => 'BDT']);
    Sanctum::actingAs($user);

    $res = $this->getJson('/api/settings')->assertOk()->json();

    expect($res['user']['email'])->toBe($user->email);
    expect($res['user']['currency'])->toBe('BDT');
    expect($res['currencies'])->not->toBeEmpty();
});

test('settings patch updates name and currency', function () {
    $user = User::factory()->create(['name' => 'Old', 'currency' => 'BDT']);
    Sanctum::actingAs($user);

    $this->patchJson('/api/settings', [
        'name' => 'New Name',
        'currency' => 'USD',
    ])->assertOk()
        ->assertJsonPath('user.name', 'New Name')
        ->assertJsonPath('user.currency', 'USD');

    expect($user->fresh()->name)->toBe('New Name');
    expect($user->fresh()->currency)->toBe('USD');
});

test('settings rejects invalid currency', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->patchJson('/api/settings', [
        'currency' => 'XXX',
    ])->assertUnprocessable();
});

test('settings password update works', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);
    Sanctum::actingAs($user);

    $this->putJson('/api/settings/password', [
        'current_password' => 'oldpassword',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ])->assertOk();

    expect(Hash::check('newpassword1', $user->fresh()->password))->toBeTrue();
});

test('settings password update rejects wrong current', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);
    Sanctum::actingAs($user);

    $this->putJson('/api/settings/password', [
        'current_password' => 'wrong',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ])->assertUnprocessable();
});
