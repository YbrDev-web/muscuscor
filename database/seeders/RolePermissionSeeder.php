<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // 1️⃣ Création des permissions
        $perms = [
            'modifier articles',
            'supprimer articles',
            'publier articles',
            'gérer utilisateurs',
        ];
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 2️⃣ Création des rôles
        $admin   = Role::firstOrCreate(['name' => 'admin']);
        $editeur = Role::firstOrCreate(['name' => 'editeur']);
        $read = Role::firstOrCreate(['name' => 'read_only']);


        // 3️⃣ Attribution des permissions aux rôles
        $admin->syncPermissions($perms);
        $editeur->syncPermissions([
            'modifier articles',
            'publier articles',
        ]);
        $read->syncPermissions([

        ]);

        // 4️⃣ (Optionnel) Assigner un rôle par défaut à l’utilisateur #1
        $user = \App\Models\User::find(1);
        if ($user) {
            $user->assignRole('admin');
        }
    }
}
