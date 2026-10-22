<!DOCTYPE html>
<html>
<head><title>Login</title>@include('partials.style')
</head>
<body>
<div class="card">
<h1>Log in</h1>

@if ($errors->any())
    <ul class="error">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <p><label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label></p>
    <p><label>Password <input type="password" name="password" required></label></p>
    <p><label><input type="checkbox" name="remember" value="1"> Remember me</label></p>
    <button type="submit">Log in</button>
    <a href="{{ route('register') }}">Create an account</a>
</form>

<h3>Demo logins (password: <code>password</code>)</h3>
<ul>
    <li>admin@bank.test</li>
    <li>alice@bank.test</li>
    <li>bob@bank.test</li>
</ul>
</div>
</body>
</html>
