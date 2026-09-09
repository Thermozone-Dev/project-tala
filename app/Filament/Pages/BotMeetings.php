<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;


class BotMeetings extends Page
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static ?string $navigationLabel = 'BOT Meetings';

    protected static ?string $title = 'Board of Trustees Meetings';

    protected string $view = 'filament.pages.bot-meetings';
    protected static ?int $navigationSort = 50;

    public string $activeTab = 'meet';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $isExecutive = get_executive_role($user->roles->first()?->name);
        $isTrustee = $user->hasRole('Trustee');

        return $isExecutive || $isTrustee;
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $isExecutive = get_executive_role($user->roles->first()?->name);
        $isTrustee = $user->hasRole('Trustee');

        if (!($isExecutive || $isTrustee)) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
    }
}
