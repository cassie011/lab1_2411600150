@extends('layouts.app')

@section('title', 'Edit Grade')

@section('content')

<h2>Edit Grade</h2>

<form
    method="POST"
    action="{{ route('grades.update', $grade) }}"
    class="bg-white p-4 rounded shadow-sm"
>
    @csrf
    @method('PUT')

    @include('grades._form')

    <button type="submit" class="btn btn-primary mt-3">
        Update Grade
    </button>

    <a href="{{ route('grades.show', $grade) }}" class="btn btn-outline-secondary mt-3">
        Cancel
    </a>
</form>

@endsection
