<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Livewire\CustomPersonalInfo;
use App\Models\Committee;
use App\Models\CommitteeHasTrustee;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Illuminate\Support\Facades\Schema;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        $navigation = [];

        if (Schema::hasTable('committees')) {
            $committees = Committee::all();

            foreach ($committees as $committee) {
                $navigation[] = NavigationItem::make($committee->name)
                    ->group('Committees')
                    ->isActiveWhen(fn() => request()->is('admin/committees/' . $committee->id . '*'))
                    ->url(fn (): string => CommitteeResource::getUrl('view', ['record' => $committee->id]))
                    ->visible(function () use ($committee) {
                        if (auth()->user()->hasRole('Super Admin')) return true;

                        return CommitteeHasTrustee::where('user_id', auth()->id())
                            ->where('committee_id', $committee->id)
                            ->exists();
                    });
            }
        }

        $navigation[] = NavigationItem::make('Setup MFA')
            ->group('Settings')
            ->icon(Heroicon::OutlinedLockClosed)
            ->badge(fn() => !auth()->user()->has_email_authentication && !auth()->user()->app_authentication_secret ? '!' : '',
                fn() => !auth()->user()->has_email_authentication && !auth()->user()->app_authentication_secret ? 'danger' : '')
            ->url(fn(): string => route('filament.admin.auth.profile'));

        return $panel
            ->darkMode(false)
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->registration()
            ->profile()
            ->multiFactorAuthentication([
                EmailAuthentication::make(),
                AppAuthentication::make()
            ])
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
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
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('Settings'),
                BreezyCore::make()
                    ->avatarUploadComponent(fn($fileUpload) => $fileUpload->disableLabel())
                    ->myProfileComponents(['personal_info' => CustomPersonalInfo::class])
                    ->myProfile(
                        shouldRegisterNavigation: true,
                        userMenuLabel: 'My Profile', // Customizes the 'account' link label in the panel User Menu (default = null)
                        navigationGroup: 'Settings', // Sets the navigation group for the My Profile page (default = null)
                        hasAvatars: true, // Enables the avatar upload form component (default = false)
                    )
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Committees')
                    ->icon(Heroicon::OutlinedUserGroup),
            ])
            ->navigationItems($navigation)
            ->brandLogo(asset('image/logos/brand-logo-white-bg.jpeg'))
            ->brandLogoHeight('6rem')
            ->sidebarCollapsibleOnDesktop();
    }
}
