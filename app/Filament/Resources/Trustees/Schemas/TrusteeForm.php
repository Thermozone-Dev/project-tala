<?php

namespace App\Filament\Resources\Trustees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class TrusteeForm
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
                Section::make('Additional Information')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('mobile_number')
                            ->label('Mobile Number')
                            ->tel()
                            ->telRegex('/^(?:\+63|0)9\d{9}$/')
                            ->placeholder('e.g. 0917XXXXXXXX or +63917XXXXXXXX')
                            ->required(),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->minDate(now()->subYears(150))
                            ->maxDate(now())
                            ->required(),

                        Select::make('gender')
                            ->label('Gender')
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female',
                                'Other' => 'Other',
                            ])
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->default('Active')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                                'Retired' => 'Retired',
                            ])
                            ->required(),

                        DatePicker::make('term_start')
                            ->label('Term Start'),

                        DatePicker::make('term_end')
                            ->label('Term End'),

                        ]),
            ]);
    }
}
