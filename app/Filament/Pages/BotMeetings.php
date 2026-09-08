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

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
    }
}
