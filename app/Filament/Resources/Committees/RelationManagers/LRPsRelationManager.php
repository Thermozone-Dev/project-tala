<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class LRPsRelationManager extends RelationManager
{

    protected static ?string $title = 'Lead Resource Persons';

    protected static ?string $label = 'Lead Resource Persons';
    protected static string $relationship = 'committee_has_trustees';
    protected static ?string $relatedResource = CommitteeResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('role_id', Role::where('name', 'Lead Resource Person')->value('id')))
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
            ])
            ->headerActions([
                Action::make('add_lrp')
                    ->label('Add Lead Resource Person')
                    ->schema([
                        Select::make('user_ids')
                            ->label('Lead Resource Persons')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(function () {

                                $committeeId = $this->getOwnerRecord()->id;

                                return User::query()
                                    ->whereHas('roles', fn ($q) => $q->where('name', 'lead resource person'))
                                    ->whereDoesntHave('committees', fn ($q) => $q->where('committee_id', $committeeId))
                                    ->get()
                                    ->pluck('full_name', 'id');
                            }),
                    ])
                    ->action(function (array $data) {

                        foreach ($data['user_ids'] as $user_id) {

                            $role_id = Role::where('name', 'lead resource person')->value('id');

                            $this->getOwnerRecord()
                                ->committee_has_trustees()
                                ->create([
                                    'user_id' => $user_id,
                                    'role_id' => $role_id
                                ]);
                        }
                    })
                    ->successNotificationTitle('Lead Resource Person Added'),
            ]);
    }
}
