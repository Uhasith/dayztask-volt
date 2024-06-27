<?php

namespace App\Support;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class Spotlight
{
    public function search(Request $request)
    {
        // Example of security concern
        // Guests can not search
        if (! auth()->user()) {
            return [];
        }

        return collect()
            ->merge($this->users($request->search))
            ->merge($this->projects($request->search));
    }

    // Database search
    public function projects(string $search = '')
    {
        return Project::query()
            ->where('user_id', auth()->user()->id)
            ->where('title', 'like', "%$search%")
            ->take(5)
            ->get()
            ->map(function (Project $project) {
                return [
                    'name' => 'Project'.' - '.$project->title,
                    'description' => $project->uuid,
                    'link' => '/projects',
                    'icon' => Blade::render("<x-mary-icon name='o-bolt' />"),
                ];
            });
    }

    // Database search
    public function users(string $search = '')
    {
        return User::query()
            ->where('name', 'like', "%$search%")
            ->orWhere('email', 'like', "%$search%")
            ->take(5)
            ->get()
            ->map(function (User $user) {
                return [
                    'name' => $user->name,
                    'description' => $user->email,
                    'link' => '/',
                    'icon' => Blade::render("<x-mary-icon name='o-user' />"),
                ];
            });
    }
}
