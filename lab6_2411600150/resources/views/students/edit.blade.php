@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')

<h2>Edit Student</h2>

<form
    method="POST"
    action="{{ route('students.update', $student) }}"
    class="bg-white p-4 rounded shadow-sm"
>
    @csrf
    @method('PUT')

    @include('students._form')

    <button class="btn btn-primary mt-3">
        Update Student
    </button>
</form>

@endsection