<?php

namespace App\Filament\Resources\Committees;

use App\Filament\Resources\Committees\Pages\CreateCommittee;
use App\Filament\Resources\Committees\Pages\EditCommittee;
use App\Filament\Resources\Committees\Pages\EvaluationMembers;
use App\Filament\Resources\Committees\Pages\EvaluationPeriods;
use App\Filament\Resources\Committees\Pages\ListCommittees;
use App\Filament\Resources\Committees\Pages\ViewCommittee;
use App\Filament\Resources\Committees\Pages\ViewCommitteeEvaluation;
use App\Filament\Resources\Committees\RelationManagers\MembersRelationManager;
use App\Filament\Resources\Committees\Schemas\CommitteeForm;
use App\Filament\Resources\Committees\Tables\CommitteesTable;
use App\Models\Committee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommitteeResource extends Resource
{
    protected static ?string $model = Committee::class;


    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['Super Admin',]);
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return CommitteeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommitteesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommittees::route('/'),
            'create' => CreateCommittee::route('/create'),
            'view' => ViewCommittee::route('/{record}'),
            'edit' => EditCommittee::route('/{record}/edit'),
            'evaluation-periods' => EvaluationPeriods::route('/{record}/{evaluator_id}/evaluation-periods'),
            'evaluation-members' => EvaluationMembers::route('/{record}/{evaluator_id}/{evaluation_id}/evaluation-periods/evaluation-members'),
            'view-evaluation' => ViewCommitteeEvaluation::route('/{record}/{record_id}/evaluation-periods/evaluation-members/view-evaluation'),
        ];
    }
}
