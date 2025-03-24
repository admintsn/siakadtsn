<?php

namespace App\Providers\Filament;

use App\Filament\Pages\WSDashboard;
use App\Filament\Auth\Login;
use App\Filament\Walisantri\Resources\DataSantriResource\Widgets\FormulirKedatangan;
use App\Filament\Walisantri\Resources\DataSantriResource\Widgets\Santri;
use Filament\Actions\Action;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Notifications\Livewire\Notifications;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Orion\FilamentGreeter\GreeterPlugin;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;

class WalisantriPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('walisantri')
            ->path('')
            ->login(Login::class)
            ->discoverResources(in: app_path('Filament/Walisantri/Resources'), for: 'App\\Filament\\Walisantri\\Resources')
            ->discoverPages(in: app_path('Filament/Walisantri/Pages'), for: 'App\\Filament\\Walisantri\\Pages')
            ->discoverClusters(in: app_path('Filament/Walisantri/Clusters'), for: 'App\\Filament\\Walisantri\\Clusters')
            ->pages([
                WSDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Walisantri/Widgets'), for: 'App\\Filament\\Walisantri\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->colors([
                'danger' => "#9e5d4b",
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => "#d3c281",
                'success' => "#274043",
                'warning' => Color::Orange,
            ])
            ->font('SF Pro Display')
            ->brandLogo(fn() => view('filament.logo'))
            ->brandLogoHeight('auto')
            ->favicon(asset('favicon-32x32.png'))
            ->navigationGroups([

                NavigationGroup::make()
                    ->label('PSB')
                    ->icon('heroicon-o-user-plus')
                    ->collapsed(),

                NavigationGroup::make()
                    ->label('Data Santri')
                    ->icon('heroicon-o-user-group')
                    ->collapsed(),

                NavigationGroup::make()
                    ->label('Nilai Imtihan')
                    ->icon('heroicon-o-academic-cap')
                    ->collapsed(),

                NavigationGroup::make()
                    ->label('Menu Mudir')
                    ->icon('heroicon-o-briefcase')
                    ->collapsed(),

            ])->bootUsing(function () {
                Notifications::alignment(Alignment::Right);
                Notifications::verticalAlignment(VerticalAlignment::End);
            })
            ->unsavedChangesAlerts()
            ->defaultThemeMode(ThemeMode::Light)
            ->topNavigation()
            ->maxContentWidth('full')
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->sidebarFullyCollapsibleOnDesktop()
            ->plugins([
                SpotlightPlugin::make(),
                // FilamentClearCachePlugin::make(),
            ])
            ->widgets([
                // Widgets\AccountWidget::class,
                FormulirKedatangan::class,
                Santri::class,
            ])
            ->viteTheme('resources/css/filament/walisantri/theme.css');
    }
}
