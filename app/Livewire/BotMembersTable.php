<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class BotMembersTable extends Component implements HasTable, HasSchemas, HasActions
{
    use InteractsWithTable;
    use InteractsWithSchemas;
    use InteractsWithActions;


    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('roles', function (Builder $query) {
                        $query->where('name', 'trustee');
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
    public function render() : View
    {
        return view('livewire.bot-members-table');
    }
}
