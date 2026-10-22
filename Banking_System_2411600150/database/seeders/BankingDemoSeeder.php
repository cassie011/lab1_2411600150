<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Demo data only. Run: php artisan db:seed --class=BankingDemoSeeder
class BankingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo User', 'password' => Hash::make('password')]
        );

        Account::firstOrCreate(
            ['account_number' => '1000000001'],
            ['user_id' => $user->id, 'balance' => 500.00, 'minimum_balance' => 100.00]
        );

        Account::firstOrCreate(
            ['account_number' => '1000000002'],
            ['user_id' => $user->id, 'balance' => 1000.00, 'minimum_balance' => 100.00]
        );
    }
}
