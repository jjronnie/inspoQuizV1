<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $adminRole->givePermissionTo(Permission::all());

        $this->command->info('Roles and permissions seeded successfully!');

        // Create or update the admin
        $admin = User::updateOrCreate(
            ['email' => 'ronaldjjuuko7@gmail.com'],
            [
                'name' => 'ADMIN',
                'password' => Hash::make('88928892'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
        $this->command->info('Admin seeded successfully!');


          // Create or update the user 
        $user = User::updateOrCreate(
            ['email' => 'techtower.ug@gmail.com'],
            [
                'name' => 'USER',
                'password' => Hash::make('88928892'),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('user');
        $this->command->info('User seeded successfully!');

    }
}
