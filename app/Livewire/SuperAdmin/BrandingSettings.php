<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BrandingSettings extends Component
{
    use WithFileUploads;

    // General
    public string $siteName        = '';
    public string $siteTagline     = '';
    public string $metaTitle       = '';
    public string $metaDescription = '';
    public string $supportEmail    = '';
    public string $supportPhone    = '';
    public string $copyrightText   = '';

    // Appearance
    public string $primaryColor    = '#6366f1';
    public ?string $currentLogo    = null;
    public ?string $currentFavicon = null;
    public $logoUpload             = null;
    public $faviconUpload          = null;

    // Analytics / Scripts
    public string $googleAnalyticsId   = '';
    public string $customHeadScripts   = '';

    // UI state
    public string $statusMessage = '';
    public string $statusType    = 'success';
    public string $activeTab     = 'branding';

    public function mount(): void
    {
        $s = SiteSetting::all_map();

        $this->siteName           = $s['site_name']          ?? 'WACRM';
        $this->siteTagline        = $s['site_tagline']        ?? '';
        $this->metaTitle          = $s['meta_title']          ?? '';
        $this->metaDescription    = $s['meta_description']    ?? '';
        $this->supportEmail       = $s['support_email']       ?? '';
        $this->supportPhone       = $s['support_phone']       ?? '';
        $this->copyrightText      = $s['copyright_text']      ?? '';
        $this->primaryColor       = $s['primary_color']       ?? '#6366f1';
        $this->currentLogo        = $s['logo_path']           ?? null;
        $this->currentFavicon     = $s['favicon_path']        ?? null;
        $this->googleAnalyticsId  = $s['google_analytics_id'] ?? '';
        $this->customHeadScripts  = $s['custom_head_scripts'] ?? '';
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'siteName'        => 'required|string|max:80',
            'siteTagline'     => 'nullable|string|max:160',
            'metaTitle'       => 'required|string|max:160',
            'metaDescription' => 'nullable|string|max:300',
            'supportEmail'    => 'nullable|email|max:255',
            'supportPhone'    => 'nullable|string|max:30',
            'copyrightText'   => 'nullable|string|max:200',
        ]);

        SiteSetting::set('site_name',        $this->siteName);
        SiteSetting::set('site_tagline',     $this->siteTagline);
        SiteSetting::set('meta_title',       $this->metaTitle);
        SiteSetting::set('meta_description', $this->metaDescription);
        SiteSetting::set('support_email',    $this->supportEmail);
        SiteSetting::set('support_phone',    $this->supportPhone);
        SiteSetting::set('copyright_text',   $this->copyrightText);
        SiteSetting::clearAllCache();

        $this->statusMessage = '✅ General settings saved successfully!';
        $this->statusType    = 'success';
    }

    public function saveLogo(): void
    {
        $this->validate([
            'logoUpload'    => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'faviconUpload' => 'nullable|mimes:png,ico|max:512',
            'primaryColor'  => 'required|string|max:20',
        ]);

        if ($this->logoUpload) {
            // Delete old logo
            if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
                Storage::disk('public')->delete($this->currentLogo);
            }
            $path = $this->logoUpload->store('branding', 'public');
            SiteSetting::set('logo_path', $path);
            $this->currentLogo = $path;
            $this->logoUpload  = null;
        }

        if ($this->faviconUpload) {
            if ($this->currentFavicon && Storage::disk('public')->exists($this->currentFavicon)) {
                Storage::disk('public')->delete($this->currentFavicon);
            }
            $path = $this->faviconUpload->store('branding', 'public');
            SiteSetting::set('favicon_path', $path);
            $this->currentFavicon  = $path;
            $this->faviconUpload   = null;
        }

        SiteSetting::set('primary_color', $this->primaryColor);
        SiteSetting::clearAllCache();

        $this->statusMessage = '✅ Logo & appearance saved!';
        $this->statusType    = 'success';
    }

    public function removeLogo(): void
    {
        if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
            Storage::disk('public')->delete($this->currentLogo);
        }
        SiteSetting::set('logo_path', null);
        SiteSetting::clearAllCache();
        $this->currentLogo   = null;
        $this->statusMessage = 'Logo removed.';
        $this->statusType    = 'info';
    }

    public function removeFavicon(): void
    {
        if ($this->currentFavicon && Storage::disk('public')->exists($this->currentFavicon)) {
            Storage::disk('public')->delete($this->currentFavicon);
        }
        SiteSetting::set('favicon_path', null);
        SiteSetting::clearAllCache();
        $this->currentFavicon = null;
        $this->statusMessage  = 'Favicon removed.';
        $this->statusType     = 'info';
    }

    public function saveAnalytics(): void
    {
        $this->validate([
            'googleAnalyticsId' => 'nullable|string|max:50',
            'customHeadScripts' => 'nullable|string|max:5000',
        ]);

        SiteSetting::set('google_analytics_id', $this->googleAnalyticsId);
        SiteSetting::set('custom_head_scripts', $this->customHeadScripts);
        SiteSetting::clearAllCache();

        $this->statusMessage = '✅ Analytics & scripts saved!';
        $this->statusType    = 'success';
    }

    public function render()
    {
        return view('livewire.super-admin.branding-settings')
            ->layout('layouts.super-admin');
    }
}
