<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected string $view = 'filament.resources.reports.pages.view-report';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_in_new_tab')
                ->label('Open PDF in new tab')
                ->url(fn () => route('preview-report',['report' => $this->record->id]))
                ->openUrlInNewTab()
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->iconPosition('after'),
            Action::make('download')
                ->url(fn () => route('preview-report',['report' => $this->record->id,'download' => true]))
                ->icon(Heroicon::OutlinedArrowDownTray),
            EditAction::make()
        ];
    }


}
