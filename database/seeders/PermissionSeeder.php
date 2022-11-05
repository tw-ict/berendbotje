<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'roles', 'create-role', 'delete-role', 'update-role',
            'permissions', 'attach-permission', 'attach-role',
            'config', 'timeouts'
        ];

        foreach ($permissions as $permission) {
            Permission::factory()->create(['name' => $permission]);
        }
    }
}
