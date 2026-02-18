<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->revealable()
                    ->password()
                    ->required(),
                Select::make('roles')
                    ->relationship('roles', 'name',   fn ($query) => auth()->user()?->hasRole('Super Admin') ? $query : $query->whereNotIn('name', ['Trustee', 'Super Admin']))
                    ->multiple()
                    ->preload()
                    ->required()
                    ->searchable(),
            ]);
    }
}
