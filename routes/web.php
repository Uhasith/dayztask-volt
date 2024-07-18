<?php

use App\Livewire\Pages\Dashboard\Index as DashboardIndex;
use App\Livewire\Pages\Project\Index as ProjectIndex;
use App\Livewire\Pages\Project\Show as ProjectShow;
use App\Livewire\Pages\Project\ShowAll as ProjectShowAll;
use App\Livewire\Pages\Task\Create as TaskCreate;
use App\Livewire\Pages\Task\Update as TaskUpdate;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Full Page Components Routes
    Route::get('/dashboard', DashboardIndex::class)->lazy()->name('dashboard');
    Route::get('/projects', ProjectIndex::class)->lazy()->name('projects.index');
    Route::get('/projects/{uuid}', ProjectShow::class)->lazy()->name('projects.show');
    Route::get('/projects/show/all', ProjectShowAll::class)->lazy()->name('projects.show.all');
    Route::get('/projects/tasks/create/{uuid}', TaskCreate::class)->lazy()->name('projects.tasks.create');
    Route::get('/projects/tasks/update/{uuid}', TaskUpdate::class)->lazy()->name('projects.tasks.update');

});
