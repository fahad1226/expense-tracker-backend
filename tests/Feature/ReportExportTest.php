<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('report export csv requires authentication', function () {
    $this->getJson(
        '/api/reports/export?start_date=2026-01-01&end_date=2026-01-31&format=csv',
    )->assertUnauthorized();
});

test('report export csv streams file', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 42.5,
        'date' => '2026-01-15',
        'note' => 'Test lunch',
    ]);

    Sanctum::actingAs($user);

    $response = $this->get(
        '/api/reports/export?start_date=2026-01-01&end_date=2026-01-31&format=csv&include_transactions=1',
    );

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('csv');
    expect($response->streamedContent())->toContain('Total spend');
    expect($response->streamedContent())->toContain('42.5');
    expect($response->streamedContent())->toContain('Test lunch');
});

test('report export pdf returns pdf', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    Expense::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 10,
        'date' => '2026-03-01',
        'note' => 'X',
    ]);

    Sanctum::actingAs($user);

    $response = $this->get(
        '/api/reports/export?start_date=2026-03-01&end_date=2026-03-31&format=pdf',
    );

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('pdf');
    expect(strlen($response->getContent() ?? ''))->toBeGreaterThan(100);
});
