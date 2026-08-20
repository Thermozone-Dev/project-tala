<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Infolists\Components\ViewEntry;
class MeetingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('evaluationPeriod.formattedCoverage')
                    ->label('Evaluation period')
                    ->columnSpanFull(),
                TextEntry::make('title'),
                TextEntry::make('meetingType.name')
                    ->label('Meeting type'),
                TextEntry::make('committee.name')
                    ->label('Category')
                    ->placeholder('BOT Meetings'),
                TextEntry::make('meeting_link')
                    ->url(fn($state) => $state)
                    ->color(Color::Blue)
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconPosition(IconPosition::After)
                    ->openUrlInNewTab()
                    ->placeholder('-'),
                TextEntry::make('scheduled_at')
                    ->dateTime()
                    ->formatStateUsing(fn($state) => $state->format('M d, Y H:i A')),
                TextEntry::make('createdBy.name')
                    ->label('Created by'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
