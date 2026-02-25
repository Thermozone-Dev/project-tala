<?php

namespace App\Filament\Resources\EvaluationForms\Schemas;

use App\Models\EvaluationForm;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EvaluationFormInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('pdf_template_id')
                    ->numeric(),
                TextEntry::make('shortcode'),
                TextEntry::make('title'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (EvaluationForm $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
