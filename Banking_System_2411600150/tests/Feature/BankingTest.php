<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\LowBalanceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BankingTest extends TestCase
{
    use RefreshDatabase;

    private function makeAccount(User $user, $balance = 500, $minimum = 100): Account
    {
        return Account::create([
            'user_id' => $user->id,
            'account_number' => 'ACC' . uniqid(),
            'balance' => $balance,
            'minimum_balance' => $minimum,
        ]);
    }

    private function submit(User $user, Account $account, string $type, $amount)
    {
        return $this->actingAs($user)->post(route('transactions.store'), [
            'account_id' => $account->id,
            'type' => $type,
            'amount' => $amount,
        ]);
    }

    public function test_relationships_work(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user);
        $this->submit($user, $account, 'deposit', 10);

        $this->assertTrue($account->user->is($user));
        $this->assertCount(1, $account->transactions);
        $this->assertTrue($account->transactions->first()->account->is($account));
    }

    public function test_observer_is_registered(): void
    {
        $this->assertTrue(Transaction::getEventDispatcher()->hasListeners('eloquent.creating: ' . Transaction::class));
    }

    public function test_deposit_increases_balance(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user, 500);

        $this->submit($user, $account, 'deposit', 100)->assertRedirect(route('transactions.index'));

        $this->assertEquals(600.00, (float) $account->fresh()->balance);
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_withdrawal_decreases_balance(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user, 500);

        $this->submit($user, $account, 'withdrawal', 100)->assertRedirect(route('transactions.index'));

        $this->assertEquals(400.00, (float) $account->fresh()->balance);
    }

    public function test_withdrawal_below_minimum_is_rejected(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user, 500, 100);

        $this->submit($user, $account, 'withdrawal', 450)->assertSessionHasErrors('amount');

        $this->assertEquals(500.00, (float) $account->fresh()->balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_withdrawal_more_than_balance_is_rejected(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user, 500, 100);

        $this->submit($user, $account, 'withdrawal', 600)->assertSessionHasErrors('amount');

        $this->assertEquals(500.00, (float) $account->fresh()->balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_validation_rules(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user);

        $this->submit($user, $account, 'transfer', 10)->assertSessionHasErrors('type');
        $this->submit($user, $account, 'deposit', 0)->assertSessionHasErrors('amount');
        $this->submit($user, $account, 'deposit', 'abc')->assertSessionHasErrors('amount');
        $this->actingAs($user)->post(route('transactions.store'), ['type' => 'deposit', 'amount' => 5])
            ->assertSessionHasErrors('account_id');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_user_cannot_use_someone_elses_account(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $account = $this->makeAccount($owner, 500);

        $this->submit($other, $account, 'withdrawal', 100)->assertForbidden();

        $this->assertEquals(500.00, (float) $account->fresh()->balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('transactions.index'))->assertRedirect();
    }

    public function test_failed_database_transaction_rolls_back_everything(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user, 500);

        try {
            DB::transaction(function () use ($account) {
                Transaction::create(['account_id' => $account->id, 'type' => 'deposit', 'amount' => 50]);
                throw new \RuntimeException('simulated crash');
            });
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertEquals(500.00, (float) $account->fresh()->balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_low_balance_notification_is_sent_once(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user, 500, 100);

        // 500 -> 400: still above the minimum, no notification
        $this->submit($user, $account, 'withdrawal', 100);
        $this->assertCount(0, $user->fresh()->notifications);

        // 400 -> 100: reaches the minimum, one notification
        $this->submit($user, $account, 'withdrawal', 300);
        $user = $user->fresh();
        $this->assertCount(1, $user->notifications);
        $this->assertEquals(LowBalanceNotification::class, $user->notifications->first()->type);

        // 100 -> 99 is rejected, so no second notification
        $this->submit($user, $account, 'withdrawal', 1)->assertSessionHasErrors('amount');
        $this->assertCount(1, $user->fresh()->notifications);
    }

    public function test_deposit_never_sends_notification(): void
    {
        $user = User::factory()->create();
        $account = $this->makeAccount($user, 50, 100);

        $this->submit($user, $account, 'deposit', 10);

        $this->assertCount(0, $user->fresh()->notifications);
    }

    public function test_dashboard_stats_returns_real_numbers_for_own_accounts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $a1 = $this->makeAccount($user, 500);
        $this->makeAccount($user, 250);
        $this->makeAccount($other, 9999); // must not be counted

        $this->submit($user, $a1, 'deposit', 50);

        $response = $this->actingAs($user)->getJson('/api/dashboard/stats')->assertOk();

        $this->assertEquals(2, $response->json('total_accounts'));
        $this->assertEquals(800, $response->json('total_money'));
        $this->assertEquals(1, $response->json('total_transactions_today'));
    }

    public function test_dashboard_stats_requires_login(): void
    {
        $this->getJson('/api/dashboard/stats')->assertUnauthorized();
    }
}
