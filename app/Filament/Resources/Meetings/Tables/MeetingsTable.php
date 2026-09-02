<?php

namespace App\Filament\Resources\Meetings\Tables;

use App\Actions\ViewAgendaModal;
use App\Models\Committee;
use App\Services\DocumentService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\View;

class MeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('meetingType.name')
                    ->searchable(),
                TextColumn::make('committee.name')
                    ->label('Category')
                    ->sortable()
                    ->placeholder('BOT Meetings')
                    ->searchable(),
                TextColumn::make('meeting_link')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab()
                    ->color(Color::Blue)
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconPosition(IconPosition::After)
                    ->searchable(),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->formatStateUsing(fn($state) => $state->format('M d, Y H:i A'))
                    ->sortable(),
                // TextColumn::make('evaluationPeriod.formattedCoverage'),
                TextColumn::make('createdBy.name')
                    ->label('Created by')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->placeholder('All Categories')
                    ->options(
                        ['null' => 'BOT Meetings'] +
                        Committee::query()
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->query(function ($query, $data) {
                        if (!$data['value']) {
                            return $query;
                        }
                        if ($data['value'] === 'null') {
                            return $query->whereNull('committee_id');
                        }
                        return $query->where('committee_id', $data['value']);
                    }),
            ])
            ->recordActions([
                // \Filament\Actions\Action::make('viewLatestAgenda')
                //     ->label('View Agenda')
                //     ->icon('heroicon-o-document-text')
                //     ->visible(fn ($record) => $record->documents()->exists())
                //     ->modalContent(function ($record) {
                //         $latestDocument = $record->documents()->latest('created_at')->first();
                //         if (!$latestDocument) {
                //             return null;
                //         }
                //         $documentService = new DocumentService();
                //         $htmlContent = $documentService->getEditableContent($latestDocument);
                //         return View::make('livewire.document-highlights-modal', [
                //             'document' => $latestDocument,
                //             'htmlContent' => $htmlContent,
                //         ]);
                //     })
                //     ->modalCancelActionLabel('Close')
                //     ->modalSubmitAction(false)
                //     ->modalHeading(fn ($record) => $record->documents()->latest('created_at')->first()?->title ?? 'Latest Agenda'),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
