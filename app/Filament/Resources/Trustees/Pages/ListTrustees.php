<?php

namespace App\Filament\Resources\Trustees\Pages;

use App\Filament\Resources\Trustees\TrusteeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrustees extends ListRecords
{
    protected static string $resource = TrusteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
