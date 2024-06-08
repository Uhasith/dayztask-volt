<?php

use App\Livewire\Pages\Project\Index as ProjectIndex;
use App\Livewire\Pages\Project\Show as ProjectShow;
use App\Livewire\Pages\Dashboard\Index as DashboardIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', DashboardIndex::class)->lazy()->name('dashboard');

    Route::get('/projects', ProjectIndex::class)->lazy()->name('projects.index');
    Route::get('/projects/{project}', ProjectShow::class)->lazy()->name('projects.show');

});
