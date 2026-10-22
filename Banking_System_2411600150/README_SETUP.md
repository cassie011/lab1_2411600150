# Simple Banking System – Setup & Testing Guide

> **Honest status:** I wrote this code but could NOT run it. My environment has no PHP, Composer, or internet, and your new Laravel project was not uploaded. Nothing below is "tested" until you run `php artisan test` and the manual checks on your computer. The test file `tests/Feature/BankingTest.php` is included for that.
> The files are written for Laravel 10/11/12. Tell me your Laravel version (`php artisan --version`) and any error you get, and I will fix it.

## 1. Features implemented (in code, untested until you run it)
- Migrations: `accounts`, `transactions` (type enum includes `transfer`), `notifications`. Foreign keys use `restrictOnDelete` (you cannot delete a user/account that has money records).
- Models `Account`, `Transaction` with fillable + relationships.
- `AccountObserver` (observes `Transaction` `creating`): locks the account row (`lockForUpdate`), updates the balance, rejects bad withdrawals with custom exceptions. The controller never changes the balance.
- `TransactionController` (`index`, `create`, `store`): validation, ownership check (403), `DB::transaction()`, friendly errors.
- `LowBalanceNotification` (database channel), sent only after commit and only when the balance crosses down to the minimum.
- `DashboardController` + `GET /api/dashboard/stats` + a page that polls immediately and every 30 s with error handling.
- Feature tests, ready-login seeder (`BankSeeder`), written analysis (`Written_Analysis.md`).

## 2. Files
**Created (copy into your project, same paths):**
```
app/Exceptions/{BankingException,InsufficientFundsException,MinimumBalanceException,InvalidTransactionException}.php
app/Models/{Account,Transaction}.php
app/Observers/AccountObserver.php
app/Notifications/LowBalanceNotification.php
app/Http/Controllers/{TransactionController,DashboardController}.php
database/migrations/2026_10_22_00000{1,2,3}_*.php
database/seeders/BankSeeder.php
app/Http/Controllers/AuthController.php
resources/views/auth/{login,register}.blade.php
resources/views/banking/dashboard.blade.php
resources/views/transactions/{index,create}.blade.php
routes/api.php        (new in Laravel 11/12; if it already exists, add the Route lines to it)
tests/Feature/BankingTest.php
```
**Modified by you (3 small edits below):** `app/Providers/AppServiceProvider.php`, `routes/web.php`, `bootstrap/app.php` (Laravel 11/12 only).

## 3. Commands and manual edits

**A. Login system (included, no Breeze needed).** `AuthController`, `resources/views/auth/login|register.blade.php` and the `BankSeeder` give you ready-made logins. Do NOT install Breeze together with this (route name clashes).

**B. Register the observer** – in `app/Providers/AppServiceProvider.php`:
```php
use App\Models\Transaction;
use App\Observers\AccountObserver;

public function boot(): void
{
    Transaction::observe(AccountObserver::class);
}
```
(The exam says "AccountObserver", but the balance must change when a *Transaction* is created, so it observes the Transaction model.)

**C. Web routes** – add to `routes/web.php`:
```php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;

Route::redirect('/', '/banking');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/banking', [DashboardController::class, 'index'])->name('banking.dashboard');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
});
```

**D. Enable API routes (Laravel 11/12 only)** – in `bootstrap/app.php` add the `api:` line:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',   // <-- add
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```
(Laravel 10 already loads `routes/api.php`.) Check with `php artisan route:list --path=api`. It should show `GET api/dashboard/stats`.

## 4. Database setup (XAMPP MySQL)
1. Start Apache + MySQL in XAMPP. In phpMyAdmin create a database, e.g. `banking_exam`.
2. In `.env` set `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=banking_exam`, `DB_USERNAME=root`, and your own `DB_PASSWORD` (XAMPP default is empty). Do not share `.env`.
3. Run:
```
php artisan migrate
php artisan db:seed --class=BankSeeder
php artisan serve
```
Ready logins (password for all: `password`): `admin@bank.test`, `alice@bank.test`, `bob@bank.test`. Each gets one account with a 1,000.00 opening deposit (minimum balance 100). Run it with `--class=BankSeeder` exactly (if you call it from `DatabaseSeeder`, remove the `WithoutModelEvents` trait, or the observer will not run and balances stay 0). Safe to run many times. The `admin` user is a normal user here (no special role). You can also self-register at `/register`.

## 5. Testing
Automated (uses its own test database, safe for your real data; Laravel 11/12 default is in-memory SQLite, so the `pdo_sqlite` PHP extension must be on):
```
php artisan test --filter=BankingTest
```
It covers: relationships, observer registered, deposit, withdrawal, minimum balance, insufficient funds, validation, 403 on another user's account, rollback, low-balance notification and no duplicates, dashboard stats values, 401 for guests.

**Must be checked manually (not covered by tests):**
- AJAX polling every 30 s: open `/banking`, open browser DevTools → Network, filter "stats". You should see one request on load and another every 30 s. Stop `php artisan serve` to see the red error message, then start it again and it recovers.
- Concurrent withdrawals (row locking) – cannot be proven by a simple test; the lock is `lockForUpdate()` in the observer and controller.
- The pages look/work in your browser.

## 6. Safe low-balance demo (for the screenshot)
1. Log in as `alice@bank.test` / `password`. Go to **New Transaction**.
2. Pick her account (balance 1,000), type **Withdrawal**, amount **900** → balance becomes exactly 100 (the minimum), which is allowed and triggers the notification.
3. Open `/banking`: the notification appears under "Notifications".
4. phpMyAdmin → database `banking_exam` → table `notifications` → **Browse**. The `data` column holds the message.
To repeat: deposit 900 back into the same account, then withdraw 900 again (this creates a second, legitimate notification). Do NOT use `migrate:fresh` unless you are fine with deleting all data in this new project's database.

## 7. Screenshot checklist (for the instructor's PDF)
1. `app/Observers/AccountObserver.php` open in your editor (whole `creating` method visible).
2. `app/Http/Controllers/TransactionController.php` (the `store` method visible).
3. `routes/api.php` showing the `/dashboard/stats` route.
4. phpMyAdmin `notifications` table (Browse) showing the low-balance row.
5. `Written_Analysis.md` answers (open it / paste into Word, then screenshot).
Put all in one PDF (e.g. paste images in Word → Save as PDF).

## 8. GitHub
```
git check-ignore -v .env      # must print a line (Laravel's default .gitignore ignores .env)
git status                    # .env must NOT be listed
git init && git add . && git commit -m "Banking system laboratory exam"
git branch -M main
git remote add origin <your-repo-url>
git push -u origin main
```
Check `.env.example` contains no real passwords or keys.

## 9. Remaining issues you may need to handle manually
- Nothing was executed by me; fix any version-specific errors (tell me and I will adjust).
- Timezone: "transactions today" uses the app timezone (`APP_TIMEZONE` in `.env` / `config/app.php`, default UTC). Set it to your local timezone so "today" matches your clock.
- `lockForUpdate()` locks rows on MySQL; SQLite (used by tests) ignores it.
- Transfers: the DB column supports `transfer`, but the controller rejects it, as the exam requires. No transfer code was written.
- `max:100000000` on the amount is my extra safety limit (the column is `decimal(12,2)`).
