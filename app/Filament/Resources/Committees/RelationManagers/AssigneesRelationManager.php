<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AssigneesRelationManager extends RelationManager
{
    protected static ?string $title = 'Assignees';

    protected static string $relationship = 'roles';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role_id')
                    ->label('Roles')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        $roles = Role::whereNotIn('name', ['Super Admin'])->orderBy('id', 'asc')->pluck('name', 'id')->toArray();

                        return $roles;
                    })
                    ->required(),
                Select::make('user_id')
                    ->label('Users')
                    ->searchable()
                    ->options(function (){
                        $id = DB::table('model_has_roles')->where('committee_id', $this->getOwnerRecord()->id)->pluck('model_id');
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
                TextColumn::make('name')->label('Role'),
                TextColumn::make('users.name')->label('Name'),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->button()
                    ->label('Delete')
                ->action(function ($record) {
                    $record->delete();
                })
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('New Assignee')
                    ->action(function (array $data){
                        DB::table('model_has_roles')->insert([
                            'committee_id' => $this->getOwnerRecord()->id,
                            'model_id' => $data['user_id'],
                            'role_id' => $data['role_id'],
                            'model_type' => User::class,
                        ]);
                    }),
            ]);
    }
}
