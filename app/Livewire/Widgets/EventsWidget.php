<?php

namespace App\Livewire\Widgets;

use App\Models\Event;
use Carbon\Carbon;
use Closure;
use \Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;
use Guava\Calendar\Actions\CreateAction;
use Filament\Forms\Form;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Log;

class EventsWidget extends CalendarWidget
{
    protected bool $eventClickEnabled = true;
    // protected string $calendarView = 'resourceTimeGridDay';

    function getEvents(array $fetchInfo = []): Collection|array
    {
        return Event::whereNull('user_id')->orWhereIn('user_id', auth()->user()->currentTeam->allUsers()->pluck('id'))->where('is_approved', 1)->get()->map(function($event){
            $event['title'] = $event['description'];
            return $event;
        });
    }

    public function getHeaderActions(): array
    {
        return [
                CreateAction::make('createLeaveRequest')->model(Event::class)->label('Request a leave')
        ];
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            $this->editAction(),
            $this->deleteAction(),
        ];
    }

    public function getDateSelectContextMenuActions(): array
    {
        return $this->getDateClickContextMenuActions();
    }

    public function getDateClickContextMenuActions(): array
    {
        return [
            CreateAction::make('createLeaveRequest')
                ->model(Event::class)->label('Request a leave')
                ->mountUsing(function (Form $form, array $arguments) {
                    $date = data_get($arguments, 'dateStr');

                    if ($date) {
                        $form->fill([
                            'start' => Carbon::make($date)->setHour(12),
                            'end' => Carbon::make($date)->setHour(13),
                        ]);
                    }
                }),
        ];
    }

    public function getSchema(?string $model = null): ?array
    {
        return match ($model) {
            Event::class => [
                Hidden::make('user_id')->default(auth()->user()->id)->required(),
                Textarea::make('description')->label('Reason')->rows(5),
                Group::make([
                    Radio::make('is_full_day')->label(false)->options(
                        [1 => 'Full day',
                         0 => 'Half day']
                    )->reactive()
                ]),
                Group::make([
                    DateTimePicker::make('start')
                        ->native(false)
                        ->seconds(false)
                        ->required()->minDate(Carbon::today()),
                    DateTimePicker::make('end')
                        ->native(false)
                        ->seconds(false)
                        ->required()->minDate(Carbon::today()->addDay())
                        ->hidden(function (Get $get) {
                            return $get('is_full_day') == 0;
                        }),
                ])->columns()
            ]
        };
    }

    public function getOptions(): array
    {
        return [
            'slotMinTime' => '09:00:00',
            'slotMaxTime' => '17:00:00',
        ];
    }

    public function authorize($ability, $arguments = [])
    {
        return true;
    }
}
