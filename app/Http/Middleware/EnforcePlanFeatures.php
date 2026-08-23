<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforcePlanFeatures
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $business = $user->business;
        if (!$business) {
            return $next($request);
        }

        // Gate: Meta CAPI
        if ($request->is('capi-config*') && !$business->hasFeature('capi')) {
            return redirect()->route('feature.locked', ['feature' => 'Meta CAPI & CTWA Ads Tracking']);
        }

        // Gate: AI Sales Agent
        if ($request->is('ai-agent*') && !$business->hasFeature('ai_agent')) {
            return redirect()->route('feature.locked', ['feature' => 'AI Sales Agent Automation']);
        }

        // Gate: Developer Settings / Webhooks
        if ($request->is('developer-settings*') && !$business->hasFeature('webhooks')) {
            return redirect()->route('feature.locked', ['feature' => 'Developer API & Webhook Integrations']);
        }

        return $next($request);
    }
}
