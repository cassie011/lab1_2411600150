<!DOCTYPE html>
<html>
<head><title>Register</title>@include('partials.style')
</head>
<body>
<div class="card">
<h1>Create an account</h1>

@if ($errors->any())
    <ul class="error">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf
    <p><label>Name <input type="text" name="name" value="{{ old('name') }}" required></label></p>
    <p><label>Email <input type="email" name="email" value="{{ old('email') }}" required></label></p>
    <p><label>Password (min 8) <input type="password" name="password" required></label></p>
    <p><label>Confirm password <input type="password" name="password_confirmation" required></label></p>
    <button type="submit">Register</button>
    <a href="{{ route('login') }}">Back to login</a>
</form>
</div>
</body>
</html>
