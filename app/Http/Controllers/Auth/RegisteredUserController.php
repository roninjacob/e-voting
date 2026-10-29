<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterVoterRequest;
use App\Models\Block;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', ['blocks' => Block::query()->orderBy('year_level')->orderBy('section')->get()]);
    }

    public function store(RegisterVoterRequest $request): RedirectResponse
    {
        User::create([...$request->safe()->except('password'), 'password' => Hash::make($request->string('password')->toString()), 'role' => 'voter', 'is_verified' => false]);

        return redirect()->route('login')->with('status', 'Registration submitted. Wait for an administrator to verify your account.');
    }
}
