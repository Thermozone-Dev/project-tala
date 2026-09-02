<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Models\Meeting;
use App\Services\DocumentService;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;

class MeetingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('evaluationPeriod.formattedCoverage')
                    ->label('Evaluation period')
                    ->columnSpanFull(),
                TextEntry::make('title'),
                TextEntry::make('meetingType.name')
                    ->label('Meeting type'),
                TextEntry::make('committee.name')
                    ->label('Category')
                    ->placeholder('BOT Meetings'),
                TextEntry::make('meeting_link')
                    ->url(fn($state) => $state)
                    ->color(Color::Blue)
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconPosition(IconPosition::After)
                    ->openUrlInNewTab()
                    ->placeholder('-'),
                TextEntry::make('scheduled_at')
                    ->dateTime()
                    ->formatStateUsing(fn($state) => $state->format('M d, Y h:i A')),
                TextEntry::make('createdBy.name')
                    ->label('Created by'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->formatStateUsing(fn($state) => $state->format('M d, Y h:i A')),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->formatStateUsing(fn($state) => $state->format('M d, Y h:i A')),

                Section::make()
                    ->columnSpanFull()
                    ->collapsible(false)
                    ->collapsed(false)
                    ->visible(function (Meeting $record){
                        if(!$record?->documents()->published()->exists() || get_executive_role()){
                            return false;
                        }
                        $document = $record->documents()->published()->latest('created_at')->first();

                        if(!$document){
                            return false;
                        }
                        return true;

                    })
                    ->schema([
                        ViewEntry::make('agenda')
                            ->view('filament.infolists.entries.agenda-section')
                            ->viewData(function ($record) {
                                $document = $record->documents()->latest('created_at')->first();
                                if (!$document) {
                                    return [];
                                }
                                $documentService = new DocumentService();
                                $htmlContent = $documentService->getEditableContent($document);
                                return [
                                    'document' => $document,
                                    'htmlContent' => $htmlContent,
                                ];
                            })
                    ]),
            ]);
    }
}
