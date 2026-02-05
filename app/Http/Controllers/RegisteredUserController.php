<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use function redirect;

class RegisteredUserController extends Controller
{
    //
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['email', 'required', 'string', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // bcrypt
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/')->with('success', 'Registration complete!');
    }

    public function create()
    {
        return view('auth.register');
    }
}
