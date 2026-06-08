<?php

namespace App\Filament\Resources\Meetings\Tables;

use App\Models\Meeting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn (Meeting $record) => Str::limit($record->description, 50))
                    ->tooltip(fn (Meeting $record) => $record->description)
                    ->searchable(),
                TextColumn::make('meeting_url')
                    ->label('Meeting URL')
                    ->color('info')
                    ->openUrlInNewTab()
                    ->searchable(),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status.label')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('creator.full_name')
                    ->numeric()
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
