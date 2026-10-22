<?php

namespace App\Notifications;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowBalanceNotification extends Notification
{
    use Queueable;

    public function __construct(public Account $account)
    {
    }

    public function via($notifiable): array
    {
        return ['database']; // saved in the "notifications" table
    }

    public function toArray($notifiable): array
    {
        return [
            'account_id' => $this->account->id,
            'account_number' => $this->account->account_number,
            'balance' => $this->account->balance,
            'minimum_balance' => $this->account->minimum_balance,
            'message' => 'Low balance: account ' . $this->account->account_number
                . ' has ' . number_format((float) $this->account->balance, 2)
                . ', which is at or below the minimum of '
                . number_format((float) $this->account->minimum_balance, 2) . '.',
        ];
    }
}
