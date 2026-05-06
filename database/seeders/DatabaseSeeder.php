<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(ExpenseSeeder::class);

        if ($email = env('ADMIN_EMAIL')) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => env('ADMIN_NAME', 'Administrator'),
                    'password' => \Illuminate\Support\Facades\Hash::make((string) env('ADMIN_PASSWORD', 'password')),
                    'is_admin' => true,
                    'currency' => 'BDT',
                ],
            );
        }
    }
}
