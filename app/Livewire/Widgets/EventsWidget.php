<?php

namespace App\Livewire\Widgets;

use Closure;
use Carbon\Carbon;
use Filament\Forms\Get;
use Illuminate\Support\Collection;
use App\Models\Event as EventModel;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Guava\Calendar\ValueObjects\Event;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Guava\Calendar\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use HusamTariq\FilamentTimePicker\Forms\Components\TimePickerField;
use Guava\Calendar\Widgets\CalendarWidget;

class EventsWidget extends CalendarWidget
{
    protected bool $eventClickEnabled = true;
    protected bool $dateClickEnabled = true;
    protected ?string $defaultEventClickAction = 'delete';
    protected string $calendarView = 'dayGridMonth';


    public function getEvents(array $fetchInfo = []): Collection|array
    {
        $userTimezone = Auth::user()->timezone ?? config('app.timezone');

        return EventModel::whereNull('user_id')
            ->orWhereIn('user_id', Auth::user()->currentTeam->allUsers()->pluck('id'))
            ->where('is_approved', 1)
            ->get()
            ->map(function ($model) use ($userTimezone) {
                // 🏷️ Title: prepend name if user_id exists
                $title = $model->description;
                if (!empty($model->user_id)) {
                    $title = $model->user?->name . ' - ' . $model->description;
                }

                // 🎨 Color Logic
                $color = str_contains(strtolower($model->description), 'mercantile') ? '#ff5959' : '#e8bc82';
                if (!$model->is_approved) {
                    $color = '#cccccc';
                    $title .= ' (' . __('Pending') . ')';
                } elseif ($model->is_approved && !empty($model->user_id)) {
                    $color = '#f28650';
                }

                // ⏱️ Time Logic
                if ($model->is_full_day) {
                    $start = Carbon::parse($model->start, $userTimezone)->addDays(1)->startOfDay();
                    $end = Carbon::parse($model->end, $userTimezone)->addDays(1)->startOfDay();

                    return \Guava\Calendar\ValueObjects\Event::make($model)
                        ->title($title)
                        ->start($start)
                        ->end($end)
                        ->allDay()
                        ->backgroundColor($color)
                        ->action('edit');
                } else {
                    $date = Carbon::parse($model->start)->startOfDay()->toDateString();;
                    $start = Carbon::parse("$date {$model->start_time}", $userTimezone);
                    $end = Carbon::parse("$date {$model->end_time}", $userTimezone);

                    return \Guava\Calendar\ValueObjects\Event::make($model)
                        ->title($title)
                        ->start($start)
                        ->end($end)
                        ->backgroundColor($color)
                        ->action('edit');
                }
            });
    }

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make('createLeaveRequest')
                ->label('Request a Leave')
                ->model(EventModel::class)
                ->mutateFormDataUsing(function (array $data): array {
                    if ($data['is_full_day']) {
                        $start = Carbon::parse($data['start'])->startOfDay();
                        $days = (int) $data['number_of_days'] ?: 1;
                        $end = $start->copy()->addDays($days - 1)->startOfDay();
                        $data['start'] = $start;
                        $data['end'] = $end;
                        $data['start_time'] = null;
                        $data['end_time'] = null;
                    } else {
                        $date = Carbon::parse($data['start'])->toDateString();
                        $start = Carbon::parse("$date {$data['start_time']}");
                        $end = Carbon::parse("$date {$data['end_time']}");
                        $data['start'] = $start;
                        $data['end'] = $end;
                    }

                    $data['user_id'] = Auth::id();
                    return $data;
                }),
        ];
    }

    public function getEventClickContextMenuActions(): array
    {
        return [$this->deleteAction()];
    }

    public function getDateClickContextMenuActions(): array
    {
        return [
            CreateAction::make('createLeaveRequest')
                ->model(EventModel::class)
                ->label('Request a Leave')
                ->mountUsing(fn($arguments, $form) => $form->fill([
                    'start' => data_get($arguments, 'dateStr'),
                    'number_of_days' => 1,
                ])),
        ];
    }

    public function getSchema(?string $model = null): ?array
    {
        return match ($model) {
            EventModel::class => [
                Hidden::make('user_id')->default(Auth::id()),

                Textarea::make('description')
                    ->label('Reason')
                    ->required()
                    ->rows(4)
                    ->disabled(fn() => $this->getEventRecord() && $this->getEventRecord()->user_id !== Auth::id()),

                Radio::make('is_full_day')
                    ->label('Leave Type')
                    ->options([
                        1 => 'Full Day',
                        0 => 'Half Day',
                    ])
                    ->default(1)
                    ->inline()
                    ->reactive(),

                // Full Day Fields
                Group::make([
                    DatePicker::make('start')
                        ->label('Start Date')
                        ->native(false)
                        ->required()
                        ->minDate(now()->addDays(2)->startOfDay()),

                    TextInput::make('number_of_days')
                        ->label('Number of Days')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),
                ])->visible(fn(Get $get) => $get('is_full_day') == 1)
                    ->columns(2),

                // Half Day Fields
                Group::make([
                    DatePicker::make('start')
                        ->label('Date')
                        ->native(false)
                        ->required()
                        ->minDate(now()->addDays(2)->startOfDay()),

                    TimePickerField::make('start_time')->label('Start Time')
                        ->okLabel("Confirm")->cancelLabel("Cancel")
                        ->label('Start Time')
                        ->required(),

                    TimePickerField::make('end_time')->label('End Time')
                        ->okLabel("Confirm")->cancelLabel("Cancel")
                        ->label('Start Time')
                        ->required()
                        ->after('start_time'),
                ])->visible(fn(Get $get) => $get('is_full_day') == 0)
                    ->columns(3),
            ],
        };
    }

    public function authorize($ability, $arguments = [])
    {
        return true;
    }
}
