<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Ready-to-use demo logins (password: "password") and one account each
 * with a 1,000.00 opening deposit. Safe to run many times: nothing is deleted.
 *
 *   php artisan db:seed --class=BankSeeder
 */
class BankSeeder extends Seeder
{
    public function run(): void
    {
        $demoUsers = [
            ['Bank Admin', 'admin@bank.test'],
            ['Alice Santos', 'alice@bank.test'],
            ['Bob Reyes', 'bob@bank.test'],
        ];

        foreach ($demoUsers as [$name, $email]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password')]
            );

            if (Account::where('user_id', $user->id)->exists()) {
                continue;
            }

            // The opening balance goes through a real deposit, so the AccountObserver
            // updates the balance and the transactions table matches it.
            DB::transaction(function () use ($user) {
                $account = Account::create([
                    'user_id' => $user->id,
                    'account_number' => Account::generateAccountNumber(),
                ]);

                Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'deposit',
                    'amount' => 1000,
                    'reference_number' => 'OPENING',
                    'notes' => 'Opening balance',
                ]);
            });
        }
    }
}
