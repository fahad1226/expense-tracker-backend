<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('budget overview requires authentication', function () {
    $this->getJson('/api/budgets')->assertUnauthorized();
});

test('budget overview returns spend and breakdown', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 100,
        'date' => '2026-03-10',
        'note' => 'Lunch',
    ]);

    Sanctum::actingAs($user);

    $res = $this->getJson('/api/budgets?month=2026-03')->assertOk()->json();

    expect($res['status'])->toBe('unset');
    expect((float) $res['spent'])->toEqual(100.0);
    expect($res['categoryBreakdown'])->toHaveCount(1);
    expect($res['categoryBreakdown'][0]['name'])->toBe('Food');
});

test('budget upsert sets cap and updates status', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 90,
        'date' => '2026-03-10',
    ]);

    Sanctum::actingAs($user);

    $json = $this->putJson('/api/budgets', [
        'month' => '2026-03',
        'amount' => 100,
    ])->assertOk()->json();

    expect((float) $json['budgetAmount'])->toEqual(100.0);
    expect($json['status'])->toBe('warning');
    expect((float) $json['percentUsed'])->toEqual(90.0);

    expect(Budget::query()->where('user_id', $user->id)->count())->toBe(1);
});

test('budget status over when spent exceeds cap', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 150,
        'date' => '2026-04-05',
    ]);

    Sanctum::actingAs($user);

    $this->putJson('/api/budgets', [
        'month' => '2026-04',
        'amount' => 100,
    ])->assertOk()
        ->assertJsonPath('status', 'over');
});
