<?php

namespace App\Filament\Resources\Meetings\RelationManagers;

use App\Models\MeetingAttendee;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity as ActivityLogModel;
use Illuminate\Database\Eloquent\Model;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can("Update:Meeting");
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
          ->heading('Activity Log')
          ->poll('30s')
          ->query(ActivityLogModel::query()->with(['causer', 'subject'])
              ->whereHasMorph('subject', [
                  MeetingAttendee::class,
              ], function ($query) {
                  $query->where('meeting_id', $this->getOwnerRecord()->id);
              })
              ->latest())
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

                      $labelMap = [
                          'MeetingAttendee' => 'Meeting Attendee',
                      ];
                      $subjectType = Str::headline($labelMap[class_basename($record->subject_type)]);

                      $attendee = MeetingAttendee::find($record->subject_id)?->user?->full_name ?? '—';

                      // Final fallback
                      return "{$subjectType} : {$attendee}";
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
          ])
          ->filters([
              SelectFilter::make('subject_type')
                  ->label('Subject Type')
                  ->options([
                      MeetingAttendee::class => Str::headline(class_basename(MeetingAttendee::class)),
                  ])
                  ->searchable()
                  ->preload(),
              SelectFilter::make('causer_id')
                  ->label('Causer User')
                  ->options(fn (): array => \App\Models\User::query()
                      ->whereIn('id', ActivityLogModel::query()
                          ->where('causer_type', \App\Models\User::class)
                          ->whereNotNull('causer_id')
                          ->whereHasMorph('subject', [
                              MeetingAttendee::class,
                          ], function ($query) {
                              $query->where('meeting_id', $this->getOwnerRecord()->id);
                          })
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
                          return new HtmlString('<div class="text-gray-500 p-4">No changes recorded.</div>');
                      }

                      // Handle Collection, array, or string
                      if ($state instanceof \Illuminate\Support\Collection) {
                          $properties = $state->toArray();
                      } elseif (is_string($state)) {
                          $properties = json_decode($state, true);
                      } else {
                          $properties = (array) $state;
                      }

                      if (!is_array($properties)) {
                          return new HtmlString('<div class="text-gray-500 p-4">Unable to display changes.</div>');
                      }

                      $labelMap = [
                          'attendance_status_id' => 'Attendance Status',
                          'is_late'              => 'Arrival Status',
                          'mark_timestamp'       => 'Marked At',
                          'updated_by'           => 'Updated By',
                      ];

                      $valueMap = [
                          'is_late' => fn ($value) => match((int) $value) {
                              0 => 'On Time',
                              1 => 'Late Comer',
                              default => '—',
                          },
                          'attendance_status_id' => fn ($value) =>
                              \App\Models\MeetingAttendanceStatus::find($value)?->name ?? '—',
                          'mark_timestamp' => fn ($value) =>
                          $value ? \Carbon\Carbon::parse($value)->format('M d, Y h:i A') : '—',
                          'updated_by' => fn ($value) =>
                              \App\Models\User::find($value)?->name ?? '—',
                      ];

                      $formatValue = fn($key, $value) => is_null($value)
                          ? '<span class="text-gray-400 italic">Empty</span>'
                          : e(isset($valueMap[$key]) ? ($valueMap[$key])($value) : $value);

                      $html = '<div class="p-4 space-y-4 text-sm">';

                      // Show updated attributes (old vs new)
                      if (!empty($properties['old']) && !empty($properties['attributes'])) {
                          $html .= '<div>';
                          $html .= '<p class="font-semibold text-gray-700 mb-2">Changes Made:</p>';
                          $html .= '<table class="w-full border-collapse">';
                          $html .= '<thead><tr>
                <td class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/3">Field</td>
                <td class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/3">Old Value</td>
                <td class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/3">New Value</td>
            </tr></thead>';
                          $html .= '<tbody>';

                          foreach ($properties['attributes'] as $key => $newValue) {
                              $oldValue = $properties['old'][$key] ?? null;

                              // Skip if both old and new values are the same
                              if ($oldValue === $newValue) {
                                  continue;
                              }

                              $label = $labelMap[$key] ?? str($key)->replace('_', ' ')->replace('.', ' ')->title();
                              $html .= '<tr class="border-t border-gray-100">';
                              $html .= '<td class="py-1 px-2 font-medium text-gray-700">' . e($label) . '</td>';
                              $html .= '<td class="py-1 px-2 text-red-500">' . $formatValue($key, $oldValue) . '</td>';
                              $html .= '<td class="py-1 px-2 text-green-600">' . $formatValue($key, $newValue) . '</td>';
                              $html .= '</tr>';
                          }

                          $html .= '</tbody></table>';
                          $html .= '</div>';
                      }

                      // Show created attributes
                      elseif (!empty($properties['attributes'])) {
                          $html .= '<div>';
                          $html .= '<p class="font-semibold text-gray-700 mb-2">Created With:</p>';
                          $html .= '<table class="w-full border-collapse">';
                          $html .= '<thead><tr>
                <th class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/2">Field</th>
                <th class="text-left py-1 px-2 bg-gray-100 text-gray-600 font-medium w-1/2">Value</th>
            </tr></thead>';
                          $html .= '<tbody>';

                          foreach ($properties['attributes'] as $key => $value) {
                              $label = $labelMap[$key] ?? str($key)->replace('_', ' ')->replace('.', ' ')->title();
                              $html .= '<tr class="border-t border-gray-100">';
                              $html .= '<td class="py-1 px-2 font-medium text-gray-700">' . e($label) . '</td>';
                              $html .= '<td class="py-1 px-2 text-gray-800">' . $formatValue($key, $value) . '</td>';
                              $html .= '</tr>';
                          }

                          $html .= '</tbody></table>';
                          $html .= '</div>';
                      }

                      else {
                          $html .= '<div class="text-gray-500">No changes recorded.</div>';
                      }

                      $html .= '</div>';

                      return new HtmlString($html);
                  })
                  ->modalSubmitAction(false)
                  ->modalCancelAction(fn (Action $action) => $action->label('Close')->color('primary')),
//
          ])
          ->paginated([10]);
    }
}
