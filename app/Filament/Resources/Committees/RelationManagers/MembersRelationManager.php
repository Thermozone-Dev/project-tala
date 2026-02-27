<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Models\CommitteeHasTrustee;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class MembersRelationManager extends RelationManager
{
    protected static ?string $title = 'Members';

    protected static ?string $label = 'Members';
    protected static string $relationship = 'committee_has_trustees';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role_id')
                    ->label('Roles')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        $roles = Role::whereNotIn('name', ['Super Admin','Secretariat','Trustee'])->orderBy('id', 'asc')->pluck('name', 'id')->toArray();

                        return $roles;
                    })
                    ->required(),
                Select::make('user_id')
                    ->label('Users')
                    ->searchable()
                    ->options(function (){
                        $id = CommitteeHasTrustee::where('committee_id', $this->getOwnerRecord()->id)->pluck('user_id');
                        $users = User::whereNotIn('id', $id)->pluck( 'name','id');

                        return $users;
                    })
                    ->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('role.name')->label('Role'),
                TextColumn::make('user.name')->label('Name'),
            ])
            ->recordActions([
                ViewAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'View:Committee'))
                    ->url(fn (Model $record) => CommitteeResource::getUrl('evaluation-periods', ['record' => $this->getOwnerRecord()->id,'evaluator_id' => $record->user_id])),
                EditAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'Update:Committee')),
                DeleteAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'Delete:Committee')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->action(function (array $data){
                        CommitteeHasTrustee::insert([
                            'committee_id' => $this->getOwnerRecord()->id,
                            'user_id' => $data['user_id'],
                            'role_id' => $data['role_id'],
                        ]);
                    }),
            ]);
    }
}
