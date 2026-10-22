<!DOCTYPE html>
<html>
<head><title>Banking Dashboard</title>@include('partials.style')
</head>
<body>
<div class="card">
<h1>Banking Dashboard</h1>
<p>Logged in as {{ auth()->user()->name }}
<form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf <button type="submit">Logout</button></form></p>
<p class="nav"><a href="{{ route('banking.dashboard') }}">Dashboard</a> | <a href="{{ route('transactions.index') }}">Transactions</a> | <a href="{{ route('transactions.create') }}">New Transaction</a></p>

<p class="stat">Total accounts: <strong id="total_accounts">...</strong></p>
<p class="stat">Total money: <strong id="total_money">...</strong></p>
<p class="stat">Transactions today: <strong id="total_transactions_today">...</strong></p>
<p id="stats_error" class="error" style="display:none"></p>

<h2>Notifications</h2>
<ul>
@forelse ($notifications as $notification)
    <li>{{ $notification->data['message'] }}</li>
@empty
    <li>No new notifications.</li>
@endforelse
</ul>

<script>
function loadStats() {
    fetch("{{ url('/api/dashboard/stats') }}", {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
    .then(function (response) {
        if (!response.ok) { throw new Error('Server returned ' + response.status); }
        return response.json();
    })
    .then(function (data) {
        document.getElementById('total_accounts').textContent = data.total_accounts;
        document.getElementById('total_money').textContent = Number(data.total_money).toFixed(2);
        document.getElementById('total_transactions_today').textContent = data.total_transactions_today;
        document.getElementById('stats_error').style.display = 'none';
    })
    .catch(function (error) {
        var box = document.getElementById('stats_error'); box.textContent = 'Could not update stats: ' + error.message; box.style.display = 'block';
    });
}

loadStats();                    // immediately when the page loads
setInterval(loadStats, 30000);  // then every 30 seconds
</script>
</div>
</body>
</html>
