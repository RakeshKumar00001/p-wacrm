<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Super Admin impersonation: log in AS any user account.
     * Stores the original super admin ID in the session so they can return.
     */
    public function impersonateUser(Request $request, int $userId)
    {
        // Only super admins can impersonate
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Unauthorized.');
        }

        $target = User::findOrFail($userId);

        // Cannot impersonate another super_admin
        if ($target->role === 'super_admin') {
            return back()->with('error', 'Cannot impersonate a Super Admin account.');
        }

        // Store the original super admin ID so we can switch back
        $request->session()->put('impersonating_from', Auth::id());

        Auth::login($target);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('impersonation_notice',
            "You are now logged in as {$target->name} ({$target->email}). " .
            "Click 'Return to Super Admin' to go back."
        );
    }

    /**
     * Exit impersonation and return to the Super Admin account.
     */
    public function stopImpersonating(Request $request)
    {
        $originalId = $request->session()->pull('impersonating_from');

        if (!$originalId) {
            return redirect('/dashboard');
        }

        $superAdmin = User::find($originalId);

        if (!$superAdmin || $superAdmin->role !== 'super_admin') {
            Auth::logout();
            $request->session()->invalidate();
            return redirect('/login');
        }

        Auth::login($superAdmin);
        $request->session()->regenerate();

        return redirect('/super-admin')->with('success', 'Returned to Super Admin panel.');
    }

    private function redirectByRole($user)
    {
        return match($user->role) {
            'super_admin'  => redirect('/super-admin'),
            default        => redirect('/dashboard'),
        };
    }
}
