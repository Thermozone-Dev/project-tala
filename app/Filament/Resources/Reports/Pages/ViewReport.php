<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use Filament\Actions\Action;
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

            Action::make('pdf')
                ->tooltip('Download PDF')
                ->label('PDF')
                ->url(fn () => route('preview-report',['report' => $this->record->id,'download' => true]))
                ->icon(Heroicon::OutlinedArrowDownTray),
            Action::make('excel')
                ->tooltip('Download Excel')
                ->label('Excel')
                ->url(fn () => route('preview-report',['report' => $this->record->id,'excel' => true]))
                ->icon(Heroicon::OutlinedArrowDownTray),
        ];
    }


}
