<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $organizerRole = Role::firstOrCreate(['name' => 'event_organizer']);
        $participantRole = Role::firstOrCreate(['name' => 'participant']);

        // Create a default super admin user
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole($superAdminRole);

        // Create an admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole);

        // Create an organizer
        $organizer = User::factory()->create([
            'name' => 'Event Organizer',
            'email' => 'organizer@example.com',
            'password' => bcrypt('password'),
        ]);
        $organizer->assignRole($organizerRole);

        // Create a participant
        $participant = User::factory()->create([
            'name' => 'Participant User',
            'email' => 'participant@example.com',
            'password' => bcrypt('password'),
        ]);
        $participant->assignRole($participantRole);
    }
}
