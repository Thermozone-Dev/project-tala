<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Filament\Resources\Committees\CommitteeResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AllMembersRelationManager extends RelationManager
{

    protected static ?string $title = 'All Members';

    protected static ?string $label = 'All Members';
    protected static string $relationship = 'committee_has_trustees';
    protected static ?string $relatedResource = CommitteeResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')->label('Name')->searchable()->sortable(),
                TextColumn::make('role.name')->label('Role')->searchable()->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->successNotificationTitle(fn ($record) => $record->is_active ? 'Deactivated' : 'Activated')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->is_active ? Heroicon::OutlinedXCircle : Heroicon::OutlinedCheckCircle)
                    ->action(function ($record) {
                        $record->update([
                            'is_active' => !$record->is_active,
                        ]);

                        $record->save();
                        return $record;
                    })
                    ->requiresConfirmation(),
//                Action::make('Delete')
//                    ->color('danger')
//                    ->icon(Heroicon::OutlinedTrash)
//                    ->action(fn ($record) => $record->delete())
//                    ->successNotificationTitle('Deleted')
//                    ->requiresConfirmation()
            ]);
    }
}
