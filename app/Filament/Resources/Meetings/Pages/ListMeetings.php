<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListMeetings extends ListRecords
{
    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Excel')
                ->tooltip('Download Excel')
                ->label('Excel')
                ->modalHeading('Meetings')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->schema([
                    Grid::make()
                        ->schema([
                            DatePicker::make('from')
                                ->default(now()->startOfYear()),

                            DatePicker::make('until')
                                ->default(now()->endOfYear()),
                        ])
                ])
                ->action(function ($data){
                    return redirect()->route('export-meeting-attendance-report', ['data' => $data]);
                })
                ->modalSubmitActionLabel('Export'),
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $user = auth()->user();

        // Filter meetings for non-executive users
        if (!get_executive_role()) {
            $query->whereHas('attendees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query;
    }
}
