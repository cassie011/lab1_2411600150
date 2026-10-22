<!DOCTYPE html>
<html>
<head><title>New Transaction</title>@include('partials.style')
</head>
<body>
<div class="card">
<h1>New Transaction</h1>
<p class="nav"><a href="{{ route('banking.dashboard') }}">Dashboard</a> | <a href="{{ route('transactions.index') }}">Transactions</a></p>

@if ($errors->any())
    <ul class="error">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('transactions.store') }}">
    @csrf
    <p>
        <label>Account
            <select name="account_id">
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                        {{ $account->account_number }} (balance {{ number_format((float) $account->balance, 2) }})
                    </option>
                @endforeach
            </select>
        </label>
    </p>
    <p>
        <label>Type
            <select name="type">
                <option value="deposit" @selected(old('type') == 'deposit')>Deposit</option>
                <option value="withdrawal" @selected(old('type') == 'withdrawal')>Withdrawal</option>
            </select>
        </label>
    </p>
    <p><label>Amount <input type="number" name="amount" step="0.01" min="1" value="{{ old('amount') }}"></label></p>
    <p><label>Reference number (optional) <input type="text" name="reference_number" value="{{ old('reference_number') }}"></label></p>
    <p><label>Notes (optional)<br><textarea name="notes">{{ old('notes') }}</textarea></label></p>
    <button type="submit">Submit</button>
</form>
</div>
</body>
</html>
