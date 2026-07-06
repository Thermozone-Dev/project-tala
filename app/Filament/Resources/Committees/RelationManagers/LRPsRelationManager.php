<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
                TextColumn::make('user.full_name')->label('Name')
                    ->searchable([
                        'first_name',
                        'middle_name',
                        'last_name',
                        'suffix',
                    ])->sortable(),
                TextColumn::make('role.name')->label('Role')->searchable()->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle),
            ])
            ->toolbarActions([
                BulkAction::make('delete')
                    ->label('Detach members')
                    ->requiresConfirmation()
                    ->authorize(check_committee_permission($this->getOwnerRecord()->id,'Update:Committee'))
                    ->color('danger')
                    ->action(fn (Collection $records) => $records->each->delete()),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->visible(fn ($record) => Auth::user()->hasRole(['Super Admin','Secretariat']))
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
                // ViewAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'View:Committee'))
                //     ->url(fn (Model $record) => CommitteeResource::getUrl('evaluation-periods', ['record' => $this->getOwnerRecord()->id,'evaluator_id' => $record->user_id])),
                // EditAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'Update:Committee')),
                // DeleteAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'Delete:Committee')),
//                Action::make('Delete')
//                    ->color('danger')
//                    ->icon(Heroicon::OutlinedTrash)
//                    ->action(fn ($record) => $record->delete())
//                    ->successNotificationTitle('Deleted')
//                    ->requiresConfirmation()
            ])
            ->headerActions([
                Action::make('add_lrp')
                    ->visible(fn ($record) => Auth::user()->hasRole(['Super Admin','Secretariat']))
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
