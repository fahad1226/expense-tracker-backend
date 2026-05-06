<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('expense index includes category icon', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'icon' => 'plane',
    ]);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $response = $this->getJson('/api/expenses');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data',
        'links',
        'meta',
    ]);
    expect($response->json('meta.per_page'))->toBe(15);

    $row = $response->json('data.0');
    expect($row)->toMatchArray([
        'category' => $category->name,
        'category_icon' => 'plane',
    ]);
});

test('expense index paginates fifteen per page', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = Category::factory()->create(['user_id' => $user->id]);

    Expense::factory()->count(16)->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $page1 = $this->getJson('/api/expenses?page=1');
    $page1->assertSuccessful();
    expect($page1->json('data'))->toHaveCount(15);
    expect($page1->json('meta.total'))->toBe(16);
    expect($page1->json('meta.last_page'))->toBe(2);

    $page2 = $this->getJson('/api/expenses?page=2');
    $page2->assertSuccessful();
    expect($page2->json('data'))->toHaveCount(1);
});
