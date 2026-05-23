<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                            ->relationship('roles', 'name',   fn ($query) => auth()->user()?->hasRole('Super Admin') ? $query : $query->whereNotIn('name', ['Trustee', 'Super Admin'])->orderBy('id', 'asc'))
                            ->multiple()
                            ->preload()
                            ->required()
                            ->searchable(),
                    ]),
                    Group::make([
                        TextInput::make('password')
                            ->revealable()
                            ->password()
                            ->required(),
                        TextInput::make('confirm_password')
                            ->revealable()
                            ->same('password')
                            ->password()
                            ->required(),
                    ])
                ]),
            ]);
    }
}
