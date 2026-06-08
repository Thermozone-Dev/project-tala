<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            Section::make()
                ->columnSpanFull()
                ->columns()
                ->schema([
                    Toggle::make('is_active')->default(true),
                    Grid::make(4)
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('first_name')
                                ->required(),
                            TextInput::make('middle_name'),
                            TextInput::make('last_name')
                                ->required(),
                            TextInput::make('suffix'),
                        ]),
                    Group::make([
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        Select::make('roles')
                            ->relationship('roles', 'name',   fn ($query) => auth()->user()?->hasRole('Super Admin') ? $query : $query->whereNotIn('name', ['Super Admin'])->orderBy('id', 'asc'))
                            ->multiple()
                            ->preload()
                            ->required(),
                    ]),
                    Group::make([
                        TextInput::make('password')
                            ->revealable()
                            ->password()
                            ->hint(fn (string $operation): string => $operation === 'edit' ? 'Leave blank to keep the current password' : '')
                            ->hintColor('primary')
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        TextInput::make('confirm_password')
                            ->revealable()
                            ->same('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create'),
                    ])
                ]),
            ]);
    }
}
