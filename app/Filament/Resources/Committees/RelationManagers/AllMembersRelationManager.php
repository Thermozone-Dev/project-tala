<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Filament\Resources\Committees\CommitteeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AllMembersRelationManager extends RelationManager
{

    protected static ?string $title = 'All Members';

    protected static ?string $label = 'All Members';
    protected static string $relationship = 'committee_has_trustees';
    protected static ?string $relatedResource = CommitteeResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')->label('Name')->searchable()->sortable(),
                TextColumn::make('role.name')->label('Role')->searchable()->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->visible(fn ($record) => Auth::user()->hasRole(['Super Admin','Secretariat']))
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->successNotificationTitle(fn ($record) => $record->is_active ? 'Deactivated' : 'Activated')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->is_active ? Heroicon::OutlinedXCircle : Heroicon::OutlinedCheckCircle)
                    ->action(function ($record) {
                        $record->update([
                            'is_active' => !$record->is_active,
                        ]);

                        $record->save();
                        return $record;
                    })
                    ->requiresConfirmation(),
                ViewAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'View:Committee'))
                    ->url(fn (Model $record) => CommitteeResource::getUrl('evaluation-periods', ['record' => $this->getOwnerRecord()->id,'evaluator_id' => $record->user_id])),
                EditAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'Update:Committee')),
                DeleteAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'Delete:Committee')),
//                Action::make('Delete')
//                    ->color('danger')
//                    ->icon(Heroicon::OutlinedTrash)
//                    ->action(fn ($record) => $record->delete())
//                    ->successNotificationTitle('Deleted')
//                    ->requiresConfirmation()
            ]);
    }
}
