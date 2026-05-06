<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Budget, $this>
     */
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<SupportTicket, $this>
     */
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * @return array{id: int, name: string, email: string, currency: string}
     */
    public function toSummaryArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'currency' => $this->currency ?? 'BDT',
        ];
    }
}
