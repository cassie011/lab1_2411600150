<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['nullable', Rule::in(array_keys(User::ROLES))],

            // Lab 6: a student account must be linked to an existing student record
            'student_number' => [
                'nullable',
                'required_if:role,'.User::ROLE_STUDENT,
                'string',
                Rule::exists('students', 'student_id')->whereNull('user_id'),
            ],
        ], [
            'student_number.required_if' => 'Enter your Student ID to register as a student.',
            'student_number.exists' => 'Student ID not found, or it already has an account.',
        ]);

        $role = $request->input('role', User::ROLE_TEACHER);

        $user = DB::transaction(function () use ($request, $role) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Lab 6: role picked on the register form
            $user->forceFill(['role' => $role])->save();

            if ($role === User::ROLE_STUDENT) {
                Student::where('student_id', $request->student_number)
                    ->whereNull('user_id')
                    ->update(['user_id' => $user->id]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
