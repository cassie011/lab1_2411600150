<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;

class DashboardController extends Controller
{
    // The dashboard page (HTML). JavaScript on the page calls stats().
    public function index()
    {
        $notifications = auth()->user()->unreadNotifications;

        return view('banking.dashboard', compact('notifications'));
    }

    // GET /api/dashboard/stats  -> JSON, only for the logged-in user's accounts.
    public function stats()
    {
        $userId = auth()->id();

        $accounts = Account::where('user_id', $userId);

        return response()->json([
            'total_accounts' => (clone $accounts)->count(),
            'total_money' => round((float) (clone $accounts)->sum('balance'), 2),
            'total_transactions_today' => Transaction::whereIn('account_id', (clone $accounts)->select('id'))
                ->whereDate('created_at', today())
                ->count(),
        ]);
    }
}
