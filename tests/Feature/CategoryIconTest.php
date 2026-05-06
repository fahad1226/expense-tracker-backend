<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('category store persists icon and list returns it', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/categories', [
        'name' => 'My Category',
        'description' => 'Test',
        'icon' => 'plane',
    ])->assertCreated()
        ->assertJsonFragment(['name' => 'My Category', 'icon' => 'plane']);

    $this->getJson('/api/categories')
        ->assertSuccessful()
        ->assertJsonFragment(['name' => 'My Category', 'icon' => 'plane']);
});
