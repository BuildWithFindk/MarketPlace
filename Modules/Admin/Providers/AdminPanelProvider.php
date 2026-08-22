<?php

namespace Modules\Admin\Providers;

use A909M\FilamentStateFusion\FilamentStateFusionPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Modules\Admin\Filament\Admin\Widgets\PlatformOverview;
use Modules\Banner\BannerPlugin;
use Modules\Category\CategoryPlugin;
use Modules\Conversation\ConversationPlugin;
use Modules\Demo\App\Http\Middleware\ResolveDemoRequest;
use Modules\Listing\ListingPlugin;
use Modules\Location\LocationPlugin;
use Modules\Site\App\Http\Middleware\BootstrapAppData;
use Modules\Site\SitePlugin;
use Modules\User\UserPlugin;
use Modules\Video\VideoPlugin;
use MWGuerra\FileManager\Filament\Pages\FileManager;
use MWGuerra\FileManager\FileManagerPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->default()
            ->path('admin')
            ->login()
            ->brandLogo(fn (): string => asset('images/logo.png'))
            ->darkModeBrandLogo(fn (): string => asset('images/logo.png'))
            ->brandLogoHeight('2rem')
            ->colors(['primary' => Color::Blue])
            ->userMenuItems([
                'view-site' => MenuItem::make()
                    ->label('View Site')
                    ->icon('heroicon-o-globe-alt')
                    ->url(fn (): string => url('/'))
                    ->sort(-2),
            ])
            ->plugins([
                FilamentStateFusionPlugin::make(),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterNavigation: true,
                        navigationGroup: 'Settings',
                        hasAvatars: true,
                        userMenuLabel: 'My Profile',
                    )
                    ->enableTwoFactorAuthentication()
                    ->enableSanctumTokens(),
                FileManagerPlugin::make()->only([
                    FileManager::class,
                ]),
                BannerPlugin::make(),
                CategoryPlugin::make(),
                ConversationPlugin::make(),
                ListingPlugin::make(),
                LocationPlugin::make(),
                SitePlugin::make(),
                UserPlugin::make(),
                VideoPlugin::make(),
            ])
            ->pages([Dashboard::class])
            ->widgets([PlatformOverview::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ResolveDemoRequest::class,
                BootstrapAppData::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
