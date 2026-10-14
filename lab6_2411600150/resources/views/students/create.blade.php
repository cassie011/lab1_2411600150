@extends('layouts.app')

@section('title', 'Add Student')

@section('content')

<h2>Add Student</h2>

<form
    method="POST"
    action="{{ route('students.store') }}"
    class="bg-white p-4 rounded shadow-sm"
>
    @csrf

    @include('students._form')

    <button class="btn btn-primary mt-3">
        Save Student
    </button>
</form>

@endsection