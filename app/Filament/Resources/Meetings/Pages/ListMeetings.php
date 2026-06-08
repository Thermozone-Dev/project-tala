<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ListMeetings extends ListRecords
{
    protected static string $resource = MeetingResource::class;

    protected function getTableQuery(): Builder|Relation|null
    {
        return parent::getTableQuery()->ownedOrAssigned();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
