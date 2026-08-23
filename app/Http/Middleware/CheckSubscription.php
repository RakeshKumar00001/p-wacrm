<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Skip if not logged in or is super admin
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // 2. Fetch business
        $business = $user->business;
        if (!$business) {
            Auth::logout();
            return redirect('/login')->with('error', 'No business associated with this account.');
        }

        // 3. Skip check if request is for billing, payment callback, or logout
        if ($request->is('billing*') || $request->is('logout') || $request->is('api*')) {
            return $next($request);
        }

        // 4. Validate subscription status and expiry
        $now = now();
        // A trial is expired if expires_at is in the past, or if expires_at is null
        $isTrialExpired = ($business->status === 'trial' && (!$business->expires_at || $now->gt($business->expires_at)));
        // An active status is expired ONLY if expires_at is set and is in the past (null = lifetime/unlimited)
        $isActiveExpired = ($business->status === 'active' && $business->expires_at && $now->gt($business->expires_at));
        $isSuspended = ($business->status === 'suspended');

        if ($isTrialExpired || $isActiveExpired || $isSuspended) {
            $reason = 'Your subscription has expired. Please renew to continue using the application.';
            if ($business->status === 'trial') {
                $planModel = \App\Models\Plan::where('slug', $business->plan)->first();
                $trialDays = $planModel ? $planModel->trial_days : 7;
                $reason = "Your {$trialDays}-day free trial has expired. Please subscribe to keep using WACRM.";
            } elseif ($isSuspended) {
                $reason = 'Your business account is suspended. Please contact support.';
            }

            return redirect()->route('billing.renew')->with('error', $reason);
        }

        return $next($request);
    }
}
