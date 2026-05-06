<?php

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();

    $this->admin = User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => 'password',
    ]);
    $this->user = User::factory()->create();
});

test('support contact stores ticket and returns json', function (): void {
    $response = $this->postJson('/api/support/contact', [
        'category' => 'bug',
        'subject' => 'App crashed when saving',
        'message' => str_repeatDetailedSteps(),
        'replyEmail' => $this->user->email,
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['id', 'receivedAt']);

    $this->assertDatabaseHas('support_tickets', [
        'subject' => 'App crashed when saving',
        'reply_email' => $this->user->email,
        'user_id' => $this->user->id,
        'status' => SupportTicket::STATUS_OPEN,
    ]);
});

test('support honeypot returns fake success without persisting', function (): void {
    $response = $this->postJson('/api/support/contact', [
        'subject' => 'spam',
        'message' => str_repeatDetailedSteps(),
        'replyEmail' => 'spam@example.com',
        'website' => 'filled',
    ]);

    $response->assertCreated();
    expect(SupportTicket::query()->count())->toBe(0);
});

test('admin dashboard requires authentication', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));
});

test('admin dashboard requires admin user', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin can view dashboard', function (): void {
    SupportTicket::query()->create([
        'user_id' => $this->user->id,
        'category' => 'other',
        'subject' => 'Help',
        'message' => str_repeatDetailedSteps(),
        'reply_email' => $this->user->email,
        'status' => SupportTicket::STATUS_OPEN,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Overview', false)
        ->assertSee('Support', false);
});

test('admin can update user', function (): void {
    $target = User::factory()->create(['name' => 'Old']);

    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $target), [
            'name' => 'New Name',
            'email' => $target->email,
            'currency' => 'BDT',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($target->fresh()->name)->toBe('New Name');
});

test('admin cannot delete self', function (): void {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasErrors();

    expect(User::query()->whereKey($this->admin->id)->exists())->toBeTrue();
});

function str_repeatDetailedSteps(): string
{
    return str_repeat('Detailed steps to reproduce the issue. ', 3);
}
