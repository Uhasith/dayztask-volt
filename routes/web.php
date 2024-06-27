<?php

use App\Livewire\Pages\Dashboard\Index as DashboardIndex;
use App\Livewire\Pages\Project\Index as ProjectIndex;
use App\Livewire\Pages\Project\Show as ProjectShow;
use App\Livewire\Pages\Task\Create as TaskCreate;
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
    Route::get('/projects/tasks/create/{uuid}', TaskCreate::class)->lazy()->name('projects.tasks.create');

});
