<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $categoryDefaults = [
            'Food' => ['icon' => 'utensils'],
            'Transport' => ['icon' => 'car'],
            'Bills' => ['icon' => 'zap'],
            'Healthcare' => ['icon' => 'heart'],
            'Education' => ['icon' => 'graduation'],
            'Travel' => ['icon' => 'plane'],
            'Entertainment' => ['icon' => 'film'],
            'Shopping' => ['icon' => 'shopping'],
        ];

        $categories = collect($categoryDefaults)->map(function (array $meta, string $name) use ($user) {
            return Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                [
                    'description' => "Expenses for {$name}",
                    'icon' => $meta['icon'],
                ]
            );
        })->keyBy('name');

        $expenseTemplates = [
            ['Food', 125.50, 'Grocery shopping'],
            ['Food', 24.00, 'Lunch'],
            ['Food', 89.00, 'Dinner out'],
            ['Food', 45.00, 'Coffee and snacks'],
            ['Food', 32.99, 'Restaurant'],
            ['Transport', 24.00, 'Uber ride'],
            ['Transport', 35.00, 'Gas'],
            ['Transport', 45.00, 'Bus pass'],
            ['Transport', 18.50, 'Parking'],
            ['Bills', 89.00, 'Electric bill'],
            ['Bills', 120.00, 'Internet'],
            ['Bills', 65.00, 'Phone bill'],
            ['Bills', 95.00, 'Water bill'],
            ['Healthcare', 45.00, 'Pharmacy'],
            ['Healthcare', 120.00, 'Doctor visit'],
            ['Healthcare', 28.50, 'Vitamins'],
            ['Education', 250.00, 'Online course'],
            ['Education', 42.00, 'Books'],
            ['Education', 15.99, 'Udemy course'],
            ['Travel', 180.00, 'Hotel'],
            ['Travel', 95.00, 'Flight tickets'],
            ['Travel', 55.00, 'Travel insurance'],
            ['Entertainment', 15.99, 'Netflix'],
            ['Entertainment', 29.99, 'Spotify'],
            ['Entertainment', 45.00, 'Concert tickets'],
            ['Shopping', 32.99, 'Books'],
            ['Shopping', 55.00, 'Office supplies'],
            ['Shopping', 78.50, 'Clothing'],
        ];

        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        foreach ($expenseTemplates as $index => [$categoryName, $amount, $note]) {
            $category = $categories->get($categoryName);
            if (! $category) {
                continue;
            }

            $daysOffset = $index % 90;
            $date = $startDate->copy()->addDays($daysOffset);

            if ($date->lte($endDate)) {
                Expense::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'amount' => $amount * (0.8 + (fake()->randomFloat(2, 0, 0.4))),
                    'date' => $date->format('Y-m-d'),
                    'note' => $note,
                    'payment_method' => fake()->randomElement(['card', 'cash', 'bank_transfer']),
                ]);
            }
        }

        for ($i = 0; $i < 25; $i++) {
            $category = $categories->random();
            $date = Carbon::createFromFormat('Y-m-d', fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d'));

            Expense::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'amount' => fake()->randomFloat(2, 10, 300),
                'date' => $date->format('Y-m-d'),
                'note' => fake()->sentence(),
                'payment_method' => fake()->randomElement(['card', 'cash', 'bank_transfer']),
            ]);
        }
    }
}
