<?php

namespace App\Filament\Resources\Trustees;

use App\Filament\Resources\Trustees\Pages\CreateTrustee;
use App\Filament\Resources\Trustees\Pages\EditTrustee;
use App\Filament\Resources\Trustees\Pages\ListTrustees;
use App\Filament\Resources\Trustees\Schemas\TrusteeForm;
use App\Filament\Resources\Trustees\Schemas\TrusteeInfolist;
use App\Filament\Resources\Trustees\Tables\TrusteesTable;
use App\Models\Trustee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrusteeResource extends Resource
{
    protected static ?string $model = Trustee::class;

    protected static bool $shouldRegisterNavigation = false;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    public static function form(Schema $schema): Schema
    {
        return TrusteeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrusteeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrusteesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrustees::route('/'),
            'create' => CreateTrustee::route('/create'),
            'edit' => EditTrustee::route('/{record}/edit'),
        ];
    }
}
