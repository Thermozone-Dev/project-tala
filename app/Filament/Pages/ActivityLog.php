<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Spatie\Activitylog\Models\Activity as ActivityLogModel;
use UnitEnum;

class ActivityLog extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected string $view = 'filament.pages.activity-log';

    protected static ?string $title = 'Activity Log';

    protected static ?string $slug = 'activities';


    public function table(Table $table): Table
    {
        return $table
            ->query(ActivityLogModel::query()->with(['causer', 'subject'])->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M j, H:i')
                    ->sortable(),
                TextColumn::make('event')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('N/A'),
                TextColumn::make('subject_name')
                    ->label('Subject')
                    ->getStateUsing(function (ActivityLogModel $record): ?string {
                        if (! $record->subject_type) {
                            return null;
                        }

                        $subjectType = class_basename($record->subject_type);

                        if (! $record->subject) {
                            return "{$subjectType} ID {$record->subject_id}";
                        }

                        // Resolve the field name from protected/public $activitySubjectName
                        // and show its attribute
                        if (property_exists($record->subject, 'activitySubjectName')) {
                            $fieldName = (function () {
                                return $this->activitySubjectName;
                            })->call($record->subject);

                            $value = $record->subject->getAttribute($fieldName);
                            if (filled($value)) {
                                return "{$subjectType}: {$value}";
                            }
                        }

                        // Fallback to checking for 'name' property
                        if (isset($record->subject->name)) {
                            return "{$subjectType}: {$record->subject->name}";
                        }

                        // Final fallback
                        return "{$subjectType} ID {$record->subject_id}";
                    })
                    ->placeholder('N/A'),
                TextColumn::make('causer_name')
                    ->label('Causer')
                    ->getStateUsing(function (ActivityLogModel $record): ?string {
                        if (!$record->causer) {
                            return 'System';
                        }

                        // For User models, use the name accessor
                        if ($record->causer instanceof \App\Models\User) {
                            return $record->causer->name;
                        }

                        // Fallback for other models
                        return $record->causer->name ?? "#{$record->causer_id}";
                    })
                    ->placeholder('System')
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('Subject Type')
                    ->options(fn (): array => ActivityLogModel::query()
                        ->whereNotNull('subject_type')
                        ->select('subject_type')
                        ->distinct()
                        ->pluck('subject_type')
                        ->filter()
                        ->mapWithKeys(fn (string $type): array => [$type => class_basename($type)])
                        ->toArray()
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('causer_id')
                    ->label('Causer User')
//                    ->options(fn (): array => \App\Models\User::query()
//                        ->whereIn('id', ActivityLogModel::query()
//                            ->where('causer_type', \App\Models\User::class)
//                            ->whereNotNull('causer_id')
//                            ->select('causer_id')
//                            ->distinct()
//                        )
//                        ->orderBy('first_name')
//                        ->get(['id', 'first_name', 'last_name'])
//                        ->pluck('name', 'id')
//                        ->toArray()
//                    )
                    ->options(fn (): array => \App\Models\User::query()
                            ->whereIn('id', ActivityLogModel::query()
                                ->where('causer_type', \App\Models\User::class)
                                ->whereNotNull('causer_id')
                                ->select('causer_id')
                                ->distinct()
                            )
                            ->orderBy('first_name')
                            ->get(['id', 'first_name', 'last_name'])
                            ->mapWithKeys(fn ($user) => [
                                $user->id => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Unnamed User',
                            ])
                            ->toArray()
                        )
                    ->searchable()
                    ->preload()
                    ->query(function ($query, $state): void {
                        if (filled($state['value'])) {
                            $query
                                ->where('causer_type', \App\Models\User::class)
                                ->where('causer_id', $state['value']);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('viewProperties')
                    ->label('View Properties')
                    ->modalHeading('Properties')
                    ->modalContent(function (ActivityLogModel $record): HtmlString {
                        $state = $record->properties;

                        if (empty($state)) {
                            return new HtmlString('<div class="text-gray-500">N/A</div>');
                        }

                        $properties = is_string($state) ? json_decode($state, true) : $state;
                        if ($properties === null && json_last_error() !== JSON_ERROR_NONE) {
                            $properties = $state;
                        }

                        $json = json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                        return new HtmlString('<div class="text-sm whitespace-pre-wrap font-mono">'.e((string) $json).'</div>');
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (Action $action) => $action->label('Close')->color('primary')),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([100]);
    }
}
