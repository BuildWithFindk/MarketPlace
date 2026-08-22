<?php

namespace Modules\Site\App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Modules\Category\Models\Category;
use Modules\Location\Models\Country;
use Modules\Location\Support\CountryCodeManager;
use Modules\Site\App\Settings\GeneralSettings;
use Modules\User\App\Models\User;
use Throwable;

final class RequestAppData
{
    public function bootstrap(): void
    {
        $generalSettings = $this->resolveGeneralSettings();

        $this->applyRuntimeConfig($generalSettings);

        View::share('generalSettings', $generalSettings);
        View::share('headerLocationCountries', $this->resolveHeaderLocationCountries());
        View::share('headerNavCategories', $this->resolveHeaderNavCategories());
        View::share('headerAccountMeta', $this->resolveHeaderAccountMeta());
    }

    private function resolveGeneralSettings(): array
    {
        $fallbackName = config('app.name', 'HajurKoAfno');
        $fallbackLocale = config('app.locale', 'en');
        $fallbackCurrencies = $this->normalizeCurrencies(config('app.currencies', ['USD']));
        $fallbackDescription = 'Buy and sell everything in your area.';
        $fallbackHomeSlides = HomeSlideDefaults::defaults();
        $fallbackGoogleMapsApiKey = config('services.google_maps.api_key');
        $fallbackGoogleClientId = config('services.google.client_id');
        $fallbackGoogleClientSecret = config('services.google.client_secret');
        $fallbackFacebookClientId = config('services.facebook.client_id');
        $fallbackFacebookClientSecret = config('services.facebook.client_secret');
        $fallbackAppleClientId = config('services.apple.client_id');
        $fallbackAppleClientSecret = config('services.apple.client_secret');
        $fallbackDefaultCountryCode = (string) config('app.default_country_code', '+90');

        $generalSettings = [
            'site_name' => $fallbackName,
            'site_description' => $fallbackDescription,
            'home_slides' => $fallbackHomeSlides,
            'site_logo_url' => asset('images/logo.png'),
            'default_language' => $fallbackLocale,
            'default_country_code' => $fallbackDefaultCountryCode,
            'currencies' => $fallbackCurrencies,
            'sender_email' => config('mail.from.address', 'roshannrawal672@gmail.com'),
            'sender_name' => config('mail.from.name', $fallbackName),
            // Social links are provided via config/social.php (env-controlled). Do not set them here.
            'google_maps_enabled' => false,
            'google_maps_api_key' => $fallbackGoogleMapsApiKey,
            'google_login_enabled' => (bool) config('services.google.enabled', false),
            'google_client_id' => $fallbackGoogleClientId,
            'google_client_secret' => $fallbackGoogleClientSecret,
            'facebook_login_enabled' => (bool) config('services.facebook.enabled', false),
            'facebook_client_id' => $fallbackFacebookClientId,
            'facebook_client_secret' => $fallbackFacebookClientSecret,
            'apple_login_enabled' => (bool) config('services.apple.enabled', false),
            'apple_client_id' => $fallbackAppleClientId,
            'apple_client_secret' => $fallbackAppleClientSecret,
        ];

        try {
            $settings = app(GeneralSettings::class);
            $currencies = $this->normalizeCurrencies($settings->currencies ?? $fallbackCurrencies);
            $availableLocales = config('app.available_locales', ['en']);
            $defaultLanguage = in_array($settings->default_language, $availableLocales, true)
                ? $settings->default_language
                : $fallbackLocale;
            $googleMapsApiKey = trim((string) ($settings->google_maps_api_key ?: $fallbackGoogleMapsApiKey));
            $googleClientId = trim((string) ($settings->google_client_id ?: $fallbackGoogleClientId));
            $googleClientSecret = trim((string) ($settings->google_client_secret ?: $fallbackGoogleClientSecret));
            $facebookClientId = trim((string) ($settings->facebook_client_id ?: $fallbackFacebookClientId));
            $facebookClientSecret = trim((string) ($settings->facebook_client_secret ?: $fallbackFacebookClientSecret));
            $appleClientId = trim((string) ($settings->apple_client_id ?: $fallbackAppleClientId));
            $appleClientSecret = trim((string) ($settings->apple_client_secret ?: $fallbackAppleClientSecret));
            $defaultCountryCode = CountryCodeManager::normalizeCountryCode($settings->default_country_code ?? $fallbackDefaultCountryCode);

            return [
                'site_name' => trim((string) ($settings->site_name ?: $fallbackName)),
                'site_description' => trim((string) ($settings->site_description ?: $fallbackDescription)),
                'home_slides' => HomeSlideDefaults::normalize($settings->home_slides ?? []),
                'site_logo_url' => asset('images/logo.png'),
                'default_language' => $defaultLanguage,
                'default_country_code' => $defaultCountryCode,
                'currencies' => $currencies,
                'sender_email' => trim((string) ($settings->sender_email ?: config('mail.from.address'))),
                'sender_name' => trim((string) ($settings->sender_name ?: $fallbackName)),
                // Social links should be loaded from config/social.php (ENV-driven).
                'google_maps_enabled' => (bool) ($settings->enable_google_maps ?? false),
                'google_maps_api_key' => $googleMapsApiKey !== '' ? $googleMapsApiKey : null,
                'google_login_enabled' => (bool) ($settings->enable_google_login ?? false),
                'google_client_id' => $googleClientId !== '' ? $googleClientId : null,
                'google_client_secret' => $googleClientSecret !== '' ? $googleClientSecret : null,
                'facebook_login_enabled' => (bool) ($settings->enable_facebook_login ?? false),
                'facebook_client_id' => $facebookClientId !== '' ? $facebookClientId : null,
                'facebook_client_secret' => $facebookClientSecret !== '' ? $facebookClientSecret : null,
                'apple_login_enabled' => (bool) ($settings->enable_apple_login ?? false),
                'apple_client_id' => $appleClientId !== '' ? $appleClientId : null,
                'apple_client_secret' => $appleClientSecret !== '' ? $appleClientSecret : null,
            ];
        } catch (Throwable) {
            return $generalSettings;
        }
    }

    private function applyRuntimeConfig(array $generalSettings): void
    {
        $mapsKey = $generalSettings['google_maps_enabled']
            ? $generalSettings['google_maps_api_key']
            : null;

        Config::set([
            'app.name' => $generalSettings['site_name'],
            'app.locale' => $generalSettings['default_language'],
            'app.currencies' => $generalSettings['currencies'],
            'mail.from.address' => $generalSettings['sender_email'],
            'mail.from.name' => $generalSettings['sender_name'],
            'filament-google-maps.key' => $mapsKey,
            'filament-google-maps.keys.web_key' => $mapsKey,
            'filament-google-maps.keys.server_key' => $mapsKey,
            'services.google.client_id' => $generalSettings['google_client_id'],
            'services.google.client_secret' => $generalSettings['google_client_secret'],
            'services.google.redirect' => null,
            'services.google.enabled' => (bool) $generalSettings['google_login_enabled'],
            'services.facebook.client_id' => $generalSettings['facebook_client_id'],
            'services.facebook.client_secret' => $generalSettings['facebook_client_secret'],
            'services.facebook.redirect' => null,
            'services.facebook.enabled' => (bool) $generalSettings['facebook_login_enabled'],
            'services.apple.client_id' => $generalSettings['apple_client_id'],
            'services.apple.client_secret' => $generalSettings['apple_client_secret'],
            'services.apple.redirect' => null,
            'services.apple.stateless' => true,
            'services.apple.enabled' => (bool) $generalSettings['apple_login_enabled'],
            'money.defaults.currency' => $generalSettings['currencies'][0] ?? 'USD',
            'app.default_country_code' => $generalSettings['default_country_code'] ?? '+90',
            'app.default_country_iso2' => CountryCodeManager::iso2FromCountryCode($generalSettings['default_country_code'] ?? '+90') ?? 'TR',
        ]);
    }

    private function resolveHeaderLocationCountries(): array
    {
        try {
            return Country::headerLocationOptions();
        } catch (Throwable) {
            return [];
        }
    }

    private function resolveHeaderNavCategories(): array
    {
        try {
            return Category::headerNavigationItems();
        } catch (Throwable) {
            return [];
        }
    }

    private function resolveHeaderAccountMeta(): ?array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        $badgeCounts = $user->headerBadgeCounts();

        return [
            'name' => $user->getDisplayName(),
            'messages' => max(0, (int) ($badgeCounts['messages'] ?? 0)),
            'notifications' => max(0, (int) ($badgeCounts['notifications'] ?? 0)),
            'favorites' => max(0, (int) ($badgeCounts['favorites'] ?? 0)),
        ];
    }

    private function normalizeCurrencies(array $currencies): array
    {
        $normalized = collect($currencies)
            ->filter(fn ($currency) => is_string($currency) && trim($currency) !== '')
            ->map(fn (string $currency) => strtoupper(substr(trim($currency), 0, 3)))
            ->filter(fn (string $currency) => strlen($currency) === 3)
            ->unique()
            ->values()
            ->all();

        return $normalized !== [] ? $normalized : ['USD'];
    }
}
