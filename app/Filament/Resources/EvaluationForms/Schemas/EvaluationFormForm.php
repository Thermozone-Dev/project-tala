<?php

namespace App\Filament\Resources\EvaluationForms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EvaluationFormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pdf_template_id')
                    ->required()
                    ->numeric(),
                TextInput::make('shortcode')
                    ->required(),
                TextInput::make('title')
                    ->required(),
            ]);
    }
}
