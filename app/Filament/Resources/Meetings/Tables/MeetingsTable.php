<?php

namespace App\Filament\Resources\Meetings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('meetingType.name')
                    ->searchable(),
                TextColumn::make('committee.name')
                    ->label('Category')
                    ->placeholder('BOT Meetings')
                    ->searchable(),
                TextColumn::make('meeting_link')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab()
                    ->color(Color::Blue)
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconPosition(IconPosition::After)
                    ->searchable(),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->formatStateUsing(fn($state) => $state->format('M d, Y H:i A'))
                    ->sortable(),
                // TextColumn::make('evaluationPeriod.formattedCoverage'),
                TextColumn::make('createdBy.name')
                    ->label('Created by')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
