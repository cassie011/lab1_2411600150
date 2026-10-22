<!DOCTYPE html>
<html>
<head><title>Transactions</title>@include('partials.style')
</head>
<body>
<div class="card">
<h1>Transactions</h1>
<p class="nav"><a href="{{ route('banking.dashboard') }}">Dashboard</a> | <a href="{{ route('transactions.create') }}">New Transaction</a></p>

@if (session('success'))
    <p class="success">{{ session('success') }}</p>
@endif

<table>
    <tr><th>Date</th><th>Account</th><th>Type</th><th>Amount</th><th>Reference</th><th>Notes</th></tr>
    @forelse ($transactions as $t)
        <tr>
            <td>{{ $t->created_at }}</td>
            <td>{{ $t->account->account_number }}</td>
            <td>{{ $t->type }}</td>
            <td>{{ number_format((float) $t->amount, 2) }}</td>
            <td>{{ $t->reference_number }}</td>
            <td>{{ $t->notes }}</td>
        </tr>
    @empty
        <tr><td colspan="6">No transactions yet.</td></tr>
    @endforelse
</table>
</div>
</body>
</html>
