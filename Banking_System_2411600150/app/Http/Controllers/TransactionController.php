<?php

namespace App\Http\Controllers;

use App\Exceptions\BankingException;
use App\Models\Account;
use App\Models\Transaction;
use App\Notifications\LowBalanceNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    // List transactions of the logged-in user's accounts only.
    public function index()
    {
        $accountIds = Account::where('user_id', auth()->id())->pluck('id');

        $transactions = Transaction::with('account')
            ->whereIn('account_id', $accountIds)
            ->latest()
            ->take(50)
            ->get();

        return view('transactions.index', compact('transactions'));
    }

    // Show the form (only the user's own accounts).
    public function create()
    {
        $accounts = Account::where('user_id', auth()->id())->get();

        return view('transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        // 1. Validate the form. "transfer" is NOT allowed here.
        $data = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'type' => ['required', Rule::in(['deposit', 'withdrawal'])],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000000'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // 2. Authorization: the account must belong to the logged-in user.
        $account = Account::findOrFail($data['account_id']);
        abort_unless((int) $account->user_id === (int) auth()->id(), 403);

        // 3. Create the transaction inside a database transaction.
        //    The AccountObserver updates the balance (we do NOT do it here).
        try {
            [$before, $after] = DB::transaction(function () use ($data) {
                $locked = Account::lockForUpdate()->findOrFail($data['account_id']);
                $before = (float) $locked->balance;

                Transaction::create([
                    'account_id' => $data['account_id'],
                    'type' => $data['type'],
                    'amount' => $data['amount'],
                    'reference_number' => $data['reference_number'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                $locked->refresh();

                return [$before, (float) $locked->balance];
            });
        } catch (BankingException $e) {
            // Any exception rolls back everything done inside DB::transaction().
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['amount' => 'Something went wrong. Nothing was saved.']);
        }

        // 4. Only after the commit: low-balance notification.
        //    Send only when the balance CROSSES the limit (was above, now at or below),
        //    so the same low balance never notifies twice.
        if ($data['type'] === 'withdrawal') {
            $account->refresh();
            $minimum = (float) $account->minimum_balance;

            if ($before > $minimum && $after <= $minimum) {
                $account->user->notify(new LowBalanceNotification($account));
            }
        }

        return redirect()->route('transactions.index')
            ->with('success', ucfirst($data['type']) . ' of ' . number_format((float) $data['amount'], 2) . ' was successful.');
    }
}
