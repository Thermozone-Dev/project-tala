<?php

namespace App\Filament\Resources\BoardOfTrustees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BoardOfTrusteeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Trustee')
                            ->placeholder('-'),
                    ])
            ]);
    }
}
