<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(25)->create();

        User::factory()->create([
            'username' => 'paulido',
            'email' => 'idpuniv@gmail.com',
            'password' => bcrypt('12231381')
        ]);

        User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin')
        ]);

        
        $user_role = Role::create(['name' => 'user']);
        $admin_role = Role::create(['name' => 'admin']);
        Role::create(['name' => 'root']);
        User::where('email', 'idpuniv@gmail.com')->first()->assignRole($user_role);
        User::where('email', 'admin@admin.com')->first()->assignRole($admin_role);
    }
}
