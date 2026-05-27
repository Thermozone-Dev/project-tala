<?php

namespace App\Filament\Resources\BoardOfTrustees\Pages;

use App\Filament\Resources\BoardOfTrustees\BoardOfTrusteeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBoardOfTrustee extends EditRecord
{
    protected static string $resource = BoardOfTrusteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

}
