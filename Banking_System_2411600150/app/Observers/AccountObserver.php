<?php

namespace App\Observers;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransactionException;
use App\Exceptions\MinimumBalanceException;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Observes the Transaction model. When a Transaction is about to be created,
 * this class checks the rules and updates the account balance.
 * It is the ONLY place where the balance changes (the controller does not touch it).
 */
class AccountObserver
{
    public function creating(Transaction $transaction): void
    {
        // The balance update and the transaction record must succeed or fail together,
        // so this must run inside DB::transaction() (the controller does that).
        if (DB::transactionLevel() === 0) {
            throw new InvalidTransactionException('Transactions must be created inside DB::transaction().');
        }

        // Lock the account row so two requests cannot spend the same money at once.
        $account = Account::lockForUpdate()->find($transaction->account_id);

        if (! $account) {
            throw new InvalidTransactionException('Account not found.');
        }

        $amount = $this->toCents($transaction->amount);
        $balance = $this->toCents($account->balance);
        $minimum = $this->toCents($account->minimum_balance);

        if ($amount <= 0) {
            throw new InvalidTransactionException('Amount must be greater than zero.');
        }

        if ($transaction->type === 'deposit') {
            $newBalance = $balance + $amount;
        } elseif ($transaction->type === 'withdrawal') {
            $newBalance = $balance - $amount;

            if ($newBalance < 0) {
                throw new InsufficientFundsException('Insufficient funds. Your balance is ' . $this->money($balance) . '.');
            }
            if ($newBalance < $minimum) {
                throw new MinimumBalanceException('This withdrawal would put the balance below the minimum balance of ' . $this->money($minimum) . '.');
            }
        } else {
            throw new InvalidTransactionException('Only deposit and withdrawal are supported.');
        }

        $account->balance = number_format($newBalance / 100, 2, '.', '');
        $account->save();
        // If we return normally, Laravel now inserts the transaction row.
        // If we threw above, nothing was inserted and the balance did not change.
    }

    // Money as whole cents (integers) so we avoid decimal rounding problems.
    private function toCents($value): int
    {
        return (int) round(((float) $value) * 100);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2);
    }
}
