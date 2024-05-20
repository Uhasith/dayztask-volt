<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'info@dayzsolutions.com',
            'password' => Hash::make('elakiri123'),
        ]);

        User::factory(100)->withPersonalTeam()->create();
        Project::factory(100)->create();
    }
}
