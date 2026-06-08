<?php

namespace App\Filament\Resources\BoardOfTrustees\Pages;

use App\Filament\Resources\BoardOfTrustees\BoardOfTrusteeResource;
use Filament\Resources\Pages\ListRecords;

class ListBoardOfTrustees extends ListRecords
{
    protected static string $resource = BoardOfTrusteeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
