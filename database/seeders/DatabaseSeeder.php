<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'teamlead',
            'email' => 'teamlead@gmail.com',
            'phone'=>'0115648976',
            'password'=>Hash::make('123456789'),
        ]);
        \App\Models\User::factory()->create([
            'name' => 'junior',
            'email' => 'junior@gmail.com',
            'phone'=>'01554287290',
            'password'=>Hash::make('123456789'),
        ]);
        $this->call(TaskSeeder::class);
    }
}
