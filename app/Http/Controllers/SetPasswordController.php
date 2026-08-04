<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class SetPasswordController extends Controller
{
    public function showSetPasswordForm(Request $request, $token)
    {
        // Validates HMAC signature & expiration automatically
        if (! $request->hasValidSignature()) {
            abort(403, 'This onboarding link is invalid or has expired.');
        }
        return Inertia::render('Auth/SetPassword', [
            'token' => $token,
            'email' => $request->query('email')
        ]);
    }

    public function storePassword(Request $request)
    {
        $validatedData = $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        //validate the token against Laravel's password broker
        if (!Password::tokenExists($user, $request->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        //update password
        $user->forceFill([
            'password' => Hash::make($request->password),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        //deleting the token
        Password::deleteToken($user);

        return Inertia::location('/management/login');
    }
}
