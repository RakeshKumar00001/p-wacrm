<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\SharedInbox;
use App\Livewire\PipelineKanban;
use App\Livewire\MetaCapiConfig;
use App\Livewire\WhatsAppConfig;
use App\Livewire\TemplateManager;
use App\Livewire\BroadcastManager;
use App\Livewire\UserProfileSettings;
use App\Livewire\WorkflowAutomationBuilder;
use App\Livewire\AiAgentConfig;
use App\Livewire\DeveloperConfig;
use App\Livewire\ContactBook;
use App\Livewire\DripCampaignManager;
use App\Livewire\SuperAdmin\Overview as SuperAdminOverview;
use App\Livewire\SuperAdmin\DistributorList;
use App\Livewire\SuperAdmin\BusinessList;
use App\Livewire\SuperAdmin\BrandingSettings as SuperAdminBrandingSettings;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\LeadsController;

use App\Livewire\Auth\Register;
use App\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('landing', ['plans' => \App\Models\Plan::all()]))->name('home');

// Auth & Registration
Route::get('/login',      [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',     [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout',    [AuthController::class, 'logout'])->name('logout');
Route::get('/register',   Register::class)->name('register');

use App\Livewire\SuperAdmin\PlanSettings as SuperAdminPlanSettings;

/*
|--------------------------------------------------------------------------
| Super Admin Routes (role: super_admin)
|--------------------------------------------------------------------------
*/

Route::middleware(\App\Http\Middleware\SuperAdminMiddleware::class)
    ->prefix('super-admin')
    ->group(function () {
        Route::get('/',             SuperAdminOverview::class)->name('super-admin.overview');
        Route::get('/distributors', DistributorList::class)->name('super-admin.distributors');
        Route::get('/businesses',   BusinessList::class)->name('super-admin.businesses');
        Route::get('/plans',        SuperAdminPlanSettings::class)->name('super-admin.plans');
        Route::get('/branding',     SuperAdminBrandingSettings::class)->name('super-admin.branding');

        // One-click impersonation: log in as any business user
        Route::get('/impersonate/{userId}', [AuthController::class, 'impersonateUser'])
            ->name('super-admin.impersonate');
    });

// Stop impersonation — available to any authenticated user (the impersonated one)
Route::middleware('auth')->get('/stop-impersonating', [AuthController::class, 'stopImpersonating'])
    ->name('impersonate.stop');

/*
|--------------------------------------------------------------------------
| CRM App Routes (authenticated users)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\CheckSubscription::class, \App\Http\Middleware\EnforcePlanFeatures::class])->group(function () {
    Route::get('/dashboard',          Dashboard::class)->name('dashboard');
    Route::get('/inbox',              SharedInbox::class)->name('inbox');
    Route::get('/kanban',             PipelineKanban::class)->name('kanban');
    Route::get('/contacts',           ContactBook::class)->name('contacts');
    Route::get('/templates',          TemplateManager::class)->name('templates');
    Route::get('/broadcasts',         BroadcastManager::class)->name('broadcasts');
    Route::get('/drips',              DripCampaignManager::class)->name('drips');
    Route::get('/automations',        WorkflowAutomationBuilder::class)->name('automations');
    Route::get('/profile',            UserProfileSettings::class)->name('profile');
    Route::get('/whatsapp-config',    WhatsAppConfig::class)->name('whatsapp-config');
    Route::get('/capi-config',        MetaCapiConfig::class)->name('capi-config');
    Route::get('/ai-agent',           AiAgentConfig::class)->name('ai-agent');
    Route::get('/developer-settings', DeveloperConfig::class)->name('developer-settings');

    // Billing / Renew Subscription
    Route::get('/billing/renew',     [SubscriptionController::class, 'renew'])->name('billing.renew');
    Route::post('/billing/callback', [SubscriptionController::class, 'paymentCallback'])->name('billing.callback');

    // Feature locked indicator
    Route::get('/feature-locked', function () {
        return view('billing.feature-locked');
    })->name('feature.locked');
});

/*
|--------------------------------------------------------------------------
| Webhooks & Public APIs (verified by token/key + rate limited)
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:120,1')->group(function () {
    Route::get('/api/whatsapp/webhook',  [WhatsAppWebhookController::class, 'verify']);
    Route::post('/api/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);

    Route::get('/api/webhooks/meta-lead-ads',  [LeadsController::class, 'verifyMetaLeadAdsWebhook']);
    Route::post('/api/webhooks/meta-lead-ads', [LeadsController::class, 'handleMetaLeadAdsWebhook']);
});

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/api/leads', [LeadsController::class, 'createFromApi']);
});
