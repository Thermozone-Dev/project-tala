<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Models\CommitteeHasTrustee;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use phpDocumentor\Reflection\Types\Static_;
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
                EditAction::make()
                    ->authorize(function (){
                        if(auth()->user()->hasPermissionTo('FullAccess:Committee')) return true;

                        $committee_id = $this->getOwnerRecord()->id;
                        $user_id = auth()->user()->id;
                        $role_id = CommitteeHasTrustee::where('committee_id', $committee_id)
                            ->where('user_id', $user_id)
                            ->first()?->role_id;

                        $role = Role::where('id', $role_id)->first();
                        return $role->hasPermissionTo('Update:Committee');
                    }),
                DeleteAction::make()
                    ->authorize(function (){
                        if(auth()->user()->hasPermissionTo('FullAccess:Committee')) return true;

                        $committee_id = $this->getOwnerRecord()->id;
                        $user_id = auth()->user()->id;
                        $role_id = CommitteeHasTrustee::where('committee_id', $committee_id)
                            ->where('user_id', $user_id)
                            ->first()?->role_id;

                        $role = Role::where('id', $role_id)->first();
                        return $role->hasPermissionTo('Delete:Committee');
                    }),
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
