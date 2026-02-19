<?php

namespace App\Filament\Resources\Committees\Pages;

use App\Filament\Resources\Committees\CommitteeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCommittee extends ViewRecord
{
    protected static string $resource = CommitteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
