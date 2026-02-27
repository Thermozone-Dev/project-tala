<?php

namespace App\Filament\Resources\Committees\Pages;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Models\Committee;
use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class EvaluationMembers extends ListRecords
{
    protected static string $resource = CommitteeResource::class;

    public $record,$evaluator_id,$evaluation_id;
    protected $queryString = ['record','evaluator_id','evaluation_id'];
    protected static ?string $title = 'Evaluation Members';

    public function getBreadcrumbs(): array
    {
        $evaluator = User::find($this->evaluator_id);
        $evaluation = EvaluationPeriod::find($this->evaluation_id);
        $evaluation_period = Carbon::parse($evaluation->date_from)->format('M d Y').' - '.Carbon::parse($evaluation->date_to)->format('M d Y');
        $committee = Committee::find($this->record);
        $array = [
            $this->getResourceUrl().'/'.$this->record => $committee->name,
            $this->getResourceUrl().'/'.$this->record.'/'.$this->evaluator_id.'/evaluation-periods' => $evaluator->fullname,
            '0' => $evaluation_period
        ];
        return $array;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected function getTableQuery(): Builder|Relation|null
    {
        return TrusteeHasEvaluation::query()->where('committee_id', $this->record)->where('evaluator_id', $this->evaluator_id)->where('evaluation_id', $this->evaluation_id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form.shortcode'),
                TextColumn::make('evaluator.name'),
                TextColumn::make('member.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('Print')
                    ->authorize(check_committee_permission($this->record,'PrintEvaluation:Committee'))
                    ->icon(Heroicon::OutlinedPrinter),
                Action::make('Evaluate Attendance')
                    ->authorize(check_committee_permission($this->record,'AttendanceEvaluation:Committee'))
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck),
            ])
            ->toolbarActions([
//                BulkActionGroup::make([
//                    DetachBulkAction::make(),
//                    DeleteBulkAction::make(),
//                ]),
            ]);
    }
}
