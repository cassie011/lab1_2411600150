<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    // NOTE: the controller never uses $request->all(), so a form can never set "balance".
    protected $fillable = ['user_id', 'account_number', 'balance', 'minimum_balance'];

    protected $casts = [
        'balance' => 'decimal:2',
        'minimum_balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Random 10-digit account number that is not used yet.
    public static function generateAccountNumber(): string
    {
        do {
            $number = str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (static::where('account_number', $number)->exists());

        return $number;
    }
}
