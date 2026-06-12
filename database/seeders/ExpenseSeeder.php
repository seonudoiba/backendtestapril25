<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\User;
use Faker\Factory as Faker;

class ExpenseSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $categories = ['Office Supplies', 'Travel', 'Meals', 'Software', 'Hardware', 'Training'];
        
        $users = User::all();
        
        foreach ($users as $user) {
            // Create 10-20 expenses per user
            for ($i = 0; $i < rand(10, 20); $i++) {
                Expense::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'title' => $faker->sentence(3),
                    'amount' => $faker->randomFloat(2, 10, 1000),
                    'category' => $faker->randomElement($categories),
                    'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                ]);
            }
        }
    }
}