<?php

namespace App\Filament\Resources\Committees\RelationManagers;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Models\CommitteeHasTrustee;
use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class MembersRelationManager extends RelationManager
{
    protected static ?string $title = 'Members';

    protected static ?string $label = 'Members';
    protected static string $relationship = 'committee_has_trustees';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role_id')
                    ->label('Roles')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        $roles = Role::whereNotIn('name', ['Super Admin','Secretariat'])->orderBy('id', 'asc')->pluck('name', 'id')->toArray();

                        return $roles;
                    })
                    ->required(),
                Select::make('user_id')
                    ->preload()
                    ->relationship('user','name', modifyQueryUsing: function (Builder $query,$state,$operation) {

                        $query = $query->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['Super Admin','Secretariat']));

                        $committeeId = $this->getOwnerRecord()->id;

                        $excludedIds = CommitteeHasTrustee::where('committee_id', $committeeId)->pluck('user_id');

                        if ($operation === 'edit') {
                            $currentUserId = $state;

                            return $query->whereNotIn('id', $excludedIds)->orWhere('id', $currentUserId);
                        }
                        return $query->whereNotIn('id', $excludedIds);
                    })
                    ->searchable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')->label('Name')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable()->sortable(),
                TextColumn::make('role.name')->label('Role')->searchable()->sortable(),

                TextColumn::make('user_id')
                    ->label('')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(function ($state) {
                        $eval_period_ids = EvaluationPeriod::query()
                            ->whereHas('assignments', function ($query) use ($state) {
                                $query->where('evaluator_id', $state);
                            })->with('assignments')
                            ->where('status_id',1) // 1 = Active
                            ->pluck('id')
                            ->toArray();

                        $total_eval = TrusteeHasEvaluation::query()
                            ->whereIn('trustee_evaluation_statuses_id',[1,3]) // 1 = Draft and 3 = Pending
                            ->where('committee_id', $this->getOwnerRecord()->id)
                            ->where('evaluator_id', $state)
                            ->whereIn('evaluation_id', $eval_period_ids)
                            ->count() ?: null;

                        return $total_eval;
                    })
            ])
            ->recordActions([
                ViewAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'View:Committee'))
                    ->url(fn (Model $record) => CommitteeResource::getUrl('evaluation-periods', ['record' => $this->getOwnerRecord()->id,'evaluator_id' => $record->user_id])),
                EditAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'Update:Committee')),
                DeleteAction::make()->authorize(check_committee_permission($this->getOwnerRecord()->id,'Delete:Committee')),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
