<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin — can manage users + activities
        User::factory()->admin()->create([
            'name' => 'Kwame Mensah',
            'email' => 'admin@npontu.local',
            'password' => Hash::make('password'),
            'designation' => 'Systems Administrator',
            'phone' => '+233 20 000 0001',
        ]);

        // Lead — can create/edit activities + update status
        User::factory()->lead()->create([
            'name' => 'Abena Owusu',
            'email' => 'lead@npontu.local',
            'password' => Hash::make('password'),
            'designation' => 'Support Team Lead',
            'phone' => '+233 20 000 0002',
        ]);

        // Agent — can update status/remark only
        User::factory()->agent()->create([
            'name' => 'Kofi Asante',
            'email' => 'agent@npontu.local',
            'password' => Hash::make('password'),
            'designation' => 'Support Engineer',
            'phone' => '+233 20 000 0003',
        ]);

        // Extra agents for realistic seed data
        User::factory()->agent()->count(3)->create();
    }
}
