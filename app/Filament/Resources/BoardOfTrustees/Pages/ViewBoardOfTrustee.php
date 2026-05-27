<?php

namespace App\Filament\Resources\BoardOfTrustees\Pages;

use App\Filament\Resources\BoardOfTrustees\BoardOfTrusteeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBoardOfTrustee extends ViewRecord
{
    protected static string $resource = BoardOfTrusteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
