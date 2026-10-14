@extends('layouts.app')

@section('title', 'Record Grade')

@section('content')

<h2>Record Grade</h2>

<form
    method="POST"
    action="{{ route('grades.store') }}"
    class="bg-white p-4 rounded shadow-sm"
>
    @csrf

    @include('grades._form')

    <button type="submit" class="btn btn-primary mt-3">
        Save Grade
    </button>

    <a href="{{ route('grades.index') }}" class="btn btn-outline-secondary mt-3">
        Cancel
    </a>
</form>

@endsection
