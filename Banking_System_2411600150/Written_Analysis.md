# Written Analysis – Simple Banking System

## 1. Why is `DB::transaction()` important when transferring money from Account A to Account B?

A transfer has two steps: (1) take money from Account A, (2) add money to Account B. These two steps must happen **together or not at all**. `DB::transaction()` makes them one single unit of work.

If the system crashes after deducting from A but before adding to B, then **without** `DB::transaction()` the money just disappears. A lost money, B never got it, and the bank's records are wrong.

**With** `DB::transaction()`, the database has not "committed" anything yet when the crash happens. So it **rolls back** and undoes the deduction. Account A gets its money back, and the data stays correct. In short: it protects the data from half-finished operations (this is called atomicity).

## 2. AJAX Polling vs WebSockets

| | AJAX Polling | WebSockets |
|---|---|---|
| How it works | The browser asks the server again and again (every 30 seconds in our project). | The browser and server keep one connection open, and the server pushes new data right away. |
| Speed | Not instant. Data can be up to 30 seconds old. | Almost instant. |
| Setup | Very easy. Only JavaScript `fetch` and one route. | Harder. Needs extra tools (like Laravel Reverb or Pusher) and a running socket server. |

**Why we chose AJAX Polling:** it is simple, works on a normal XAMPP / `php artisan serve` setup without extra servers, and is easy to explain in an exam. The dashboard only shows basic totals, so it does not need instant updates.

**Main drawback:** there is a delay (up to 30 seconds), and it sends requests even when nothing has changed, which wastes some server resources.

## 3. Advantage of an Eloquent Observer instead of balance logic in the controller

- **One place for the rule.** The balance logic is written once in `AccountObserver`. Any code that creates a Transaction (controller, seeder, tinker, another page) follows the same rules automatically.
- **Cleaner controller.** The controller only validates, checks the user, and saves. It does not do money math.
- **Fewer bugs.** We cannot forget to update the balance in a new controller, and we cannot update it twice by mistake.
- **Easier to test and explain.** The rules (minimum balance, no negative balance) are in one file.

## 4. How can duplicate low-balance notifications be prevented?

In our project, we only send the notification when the balance **crosses** the limit: the balance was **above** the minimum **before** the withdrawal, and is **at or below** the minimum **after** it. If the account was already low, no new notification is sent. The notification is also sent only **after** the database transaction is committed, so a failed withdrawal never sends one.

Other ways to prevent duplicates:
- Save a flag or timestamp on the account (for example `low_balance_notified_at`) and reset it when the balance goes back above the minimum.
- Check if an unread low-balance notification for that account already exists before sending a new one.
