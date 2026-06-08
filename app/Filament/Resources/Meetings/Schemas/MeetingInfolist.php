<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class MeetingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('meeting_url')
                    ->label('Meeting URL')
                    ->color('info')
                    ->url(fn(Model $record) => $record->meeting_url)
                    ->openUrlInNewTab(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('scheduled_at')
                    ->dateTime(),
                TextEntry::make('duration_minutes')
                    ->numeric(),
                TextEntry::make('status.label')
                    ->label('Status')
                    ->numeric(),
                TextEntry::make('created_by')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
