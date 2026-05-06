<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('analytics requires authentication', function () {
    $this->getJson('/api/analytics?start_date=2026-01-01&end_date=2026-01-31')
        ->assertUnauthorized();
});

test('analytics validates date range', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/analytics?start_date=2026-01-31&end_date=2026-01-01')
        ->assertUnprocessable();
});

test('analytics aggregates expenses in range', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Food']);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 50.25,
        'date' => '2026-01-10',
        'note' => 'Lunch',
    ]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 25,
        'date' => '2026-01-12',
        'note' => 'Coffee',
    ]);
    // Outside range
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 999,
        'date' => '2025-12-01',
        'note' => 'Old',
    ]);

    Sanctum::actingAs($user);

    $res = $this->getJson(
        '/api/analytics?start_date=2026-01-01&end_date=2026-01-31&granularity=day&compare=1',
    )->assertOk()->json();

    expect($res['summary']['totalSpend'])->toBe(75.25);
    expect($res['summary']['transactionCount'])->toBe(2);
    expect($res['categoryMix'])->toHaveCount(1);
    expect($res['categoryMix'][0]['name'])->toBe('Food');
    expect($res['categoryMix'][0]['amount'])->toBe(75.25);
    expect($res['topDescriptions'])->not->toBeEmpty();
    expect($res['comparePeriod'])->not->toBeNull();
    expect($res['timeSeries']['granularity'])->toBe('day');
});

test('reports endpoint matches selected month via analytics', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 10,
        'date' => '2026-02-15',
        'note' => 'Test',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/reports?month=2026-02');
    $response->assertOk()->assertJsonPath('granularity', 'day');

    expect((float) $response->json('summary.totalSpend'))->toEqual(10.0);
});
