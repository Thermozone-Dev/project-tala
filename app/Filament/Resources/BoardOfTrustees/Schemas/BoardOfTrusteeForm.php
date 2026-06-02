<?php

namespace App\Filament\Resources\BoardOfTrustees\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BoardOfTrusteeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('id')
                            ->options(function () {
                                return User::whereHas('roles', fn ($query) =>
                                $query->whereNotIn('name', ['Super Admin','Secretariat'])
                                )
                                    ->get()
                                    ->mapWithKeys(fn ($user) => [
                                        $user->id => $user->full_name,
                                    ]);
                            })
                            ->disabled()
                            ->label('Trustee')
                    ]),
            ]);
    }
}
