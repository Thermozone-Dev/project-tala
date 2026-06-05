<?php

namespace App\Filament\Resources\BoardOfTrustees;

use App\Filament\Resources\BoardOfTrustees\Pages\CreateBoardOfTrustee;
use App\Filament\Resources\BoardOfTrustees\Pages\EditBoardOfTrustee;
use App\Filament\Resources\BoardOfTrustees\Pages\ListBoardOfTrustees;
use App\Filament\Resources\BoardOfTrustees\Pages\ViewBoardOfTrustee;
use App\Filament\Resources\BoardOfTrustees\RelationManagers\CommitteesRelationManager;
use App\Filament\Resources\BoardOfTrustees\Schemas\BoardOfTrusteeForm;
use App\Filament\Resources\BoardOfTrustees\Schemas\BoardOfTrusteeInfolist;
use App\Filament\Resources\BoardOfTrustees\Tables\BoardOfTrusteesTable;
use App\Models\BoardOfTrustee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BoardOfTrusteeResource extends Resource
{
    protected static ?string $model = BoardOfTrustee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('roles', function ($query) {
                $query->whereNotIn('name', ['Super Admin','Secretariat','Lead Resource Person']);
            });
    }

    public static function form(Schema $schema): Schema
    {
        return BoardOfTrusteeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BoardOfTrusteeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoardOfTrusteesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CommitteesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBoardOfTrustees::route('/'),
            'create' => CreateBoardOfTrustee::route('/create'),
            'view' => ViewBoardOfTrustee::route('/{record}'),
            'edit' => EditBoardOfTrustee::route('/{record}/edit'),
        ];
    }
}
