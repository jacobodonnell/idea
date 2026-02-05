<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use function redirect;
use function view;

class SessionsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'email' => ['email', 'required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        if (! Auth::attempt($attributes)) {
            return back()
                ->withErrors(['password' => 'We were unable to authenticate using the provided credentials'])
                ->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/')->with('success', 'You are now logged in.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        Auth::logout();

        return redirect('/')->with('success', 'Log out successful');
    }
}
