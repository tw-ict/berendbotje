<?php

namespace Database\Seeders;

use App\Discord\Core\Models\DiscordUser;
use App\Discord\Core\Models\Guild;
use App\Discord\Roles\Models\Permission;
use App\Discord\Roles\Models\Role;
use App\Discord\Roles\Scopes\PermissionScope;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $roles = [
            'admin' => Permission::all(),
            'moderator' => collect([]),
            'cringecounter' => collect([]),
            'mentionresponder' => collect([]),
        ];

        foreach (['timeouts', 'media-filter', 'add-cringe',
                     'delete-cringe', 'commands', 'reactions',
                     'add-mention', 'channels', 'delete-mention', 'openai'] as $permName) {
            $roles['moderator']->push(Permission::get($permName));
        }

        $roles['cringecounter']->push(Permission::get('add-cringe'));


        foreach (['add-mention', 'delete-mention'] as $permName) {
            $roles['mentionresponder']->push(Permission::get($permName));
        }

        $user = DiscordUser::find(1);
        foreach ($roles as $roleName => $permissions) {
            foreach (Guild::all() as $guild) {
                $tmpRole = Role::factory()->create([
                    'name' => $roleName,
                    'guild_id' => $guild->id,
                ]);

                $tmpRole->permissions()->attach($permissions->pluck('id'));
                $user->roles()->attach($tmpRole);
            }
        }


        $guild = Guild::find(1);
        $role = Role::factory()->create([
            'name' => 'owners',
            'guild_id' => $guild->id,
            'is_admin' => true,
        ]);
        $role->permissions()->attach(Permission::withoutGlobalScope(PermissionScope::class)->get());
        $user->roles()->attach($role);
    }

}
