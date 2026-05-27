<?php

namespace App\Filament\Resources\BoardOfTrustees\RelationManagers;

use App\Models\Committee;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class CommitteesRelationManager extends RelationManager
{
    protected static string $relationship = 'committees';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id','desc')
            ->columns([
                TextColumn::make('committee.name'),
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
                DeleteAction::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Committee')
                    ->modalHeading('Add Committee')
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('committee_ids')
                                    ->label('Committees')
                                    ->multiple()
                                    ->required()
                                    ->preload()
                                    ->options(function ($livewire) {

                                        $member = $livewire->getOwnerRecord();

                                        $existingCommitteeIds = $member->committees()
                                            ->pluck('committee_id');

                                        return Committee::query()
                                            ->whereNotIn('id', $existingCommitteeIds)
                                            ->pluck('name', 'id');
                                    })
                            ])
                    ])
                    ->createAnother(false)
                    ->action(function (array $data, $livewire) {
                        $member = $livewire->getOwnerRecord();
                        $role_id = Role::where('name', 'trustee')->value('id');

                        foreach ($data['committee_ids'] as $committee_id) {
                            $member->committees()->create([
                                'committee_id' => $committee_id,
                                'is_active' => $data['is_active'] ?? true,
                                'role_id' => $role_id
                            ]);
                        }
                    })
                    ->successNotificationTitle('Committee Added')
            ]);
    }
}
