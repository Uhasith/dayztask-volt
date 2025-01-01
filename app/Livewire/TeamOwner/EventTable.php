<?php

namespace App\Livewire\TeamOwner;

use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class EventTable extends PowerGridComponent
{
    public string $tableName = 'event-table-ib5nse-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Event::query()->whereNot('user_id')->with('user');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('event_user', function($event){
                return $event->user->name;
            })
            ->add('is_full_day', function($event){
                return $event->is_full_day ? e('Full day') : e('Half day');
            })
            ->add('start', function($event){
                return $event->start;
            })
            ->add('end', function($event){
                return $event->end;
            })
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')->hidden(),
            Column::make('User', 'event_user'),
            Column::make('Reason', 'description')->sortable(),
            Column::make('Approval', 'is_approved')->toggleable(
                trueLabel: 1,
                falseLabel: 0
            ),
            Column::make('Type', 'is_full_day'),
            Column::make('From', 'start'),
            Column::make('To', 'end'),
            Column::make('Submitted at', 'created_at')
                ->sortable()
                ->searchable()
        ];
    }

    public function onUpdatedToggleable(string|int $id, string $field, string $value): void
    {
        Log::info($field);
        Log::info($value);
        Event::query()->find($id)->update([
            $field => e($value),
        ]);
    }

    public function filters(): array
    {
        return [
        ];
    }

    // #[\Livewire\Attributes\On('edit')]
    // public function edit($rowId): void
    // {
    //     $this->js('alert('.$rowId.')');
    // }

    // public function actions(Event $row): array
    // {
    //     return [
    //         Button::add('edit')
    //             ->slot('Edit: '.$row->id)
    //             ->id()
    //             ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
    //             ->dispatch('edit', ['rowId' => $row->id])
    //     ];
    // }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
