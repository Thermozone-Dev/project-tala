<?php

namespace App\Filament\Resources\Trustees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrusteesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('user.is_active')->label('Active')->boolean(),
                TextColumn::make('user.name')
                    ->formatStateUsing(function ($state, $record){
                        return $state.' '. $record?->user?->suffix ?? null;
                    })
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email address')
                    ->searchable(),
                IconColumn::make('user.email_verified_at')
                    ->label('Email verified')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
