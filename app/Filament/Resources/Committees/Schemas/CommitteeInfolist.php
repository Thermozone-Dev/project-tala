<?php

namespace App\Filament\Resources\Committees\Schemas;


use Filament\Actions\Action;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Storage;


class CommitteeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->columnSpanFull()
                            ->formatStateUsing(fn($state) => strtoupper($state.' Committee'))
                            ->hiddenLabel()
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold),
                        TextEntry::make('description')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                        Flex::make([
                            Action::make('view_charter')
                                ->label('View Charter')
                                ->icon('heroicon-o-document-text')
                                ->color('primary')
                                ->visible(fn ($record) => filled($record->charter))
                                ->modalHeading('Committee Charter')
                                ->modalWidth('7xl')
                                ->modalContent(fn ($record) => view(
                                    'filament.components.file-preview',
                                    [
                                        'url' => Storage::disk('public')->url($record->charter),
                                    ]
                                ))
                                ->modalFooterActions([
                                    Action::make('open_new_tab')
                                        ->label('Open in New Tab')
                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                        ->url(fn ($record) => Storage::disk('public')->url($record->charter))
                                        ->openUrlInNewTab(),
                                ]),
                            Action::make('view_agenda_forecast')
                                ->label('View Agenda Forecast')
                                ->icon('heroicon-o-document-text')
                                ->color('primary')
                                ->visible(fn ($record) => filled($record->agenda_forecast))
                                ->modalHeading('Agenda Forecast')
                                ->modalWidth('7xl')
                                ->modalContent(fn ($record) => view(
                                    'filament.components.file-preview',
                                    [
                                        'url' => Storage::disk('public')->url($record->agenda_forecast),
                                    ]
                                ))
                                ->modalFooterActions([
                                    Action::make('open_new_tab')
                                        ->label('Open in New Tab')
                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                        ->url(fn ($record) => Storage::disk('public')->url($record->agenda_forecast))
                                        ->openUrlInNewTab(),
                                ]),

                        ])
                            ->gap(1)
                            ->columnSpanFull(),
                    ])
            ]);
    }
}
