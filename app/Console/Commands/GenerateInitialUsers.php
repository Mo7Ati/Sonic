<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Store;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Signature('initial:users')]
#[Description('Generate initial users')]
class GenerateInitialUsers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('shield:generate', ['--all' => true]);

        $admin = Admin::createOrFirst([
            'email' => 'admin@ps.com',
        ], [
            'name' => 'admin',
            'password' => 'password',
        ]);

        $role = Role::createOrFirst([
            'name' => 'super_admin',
        ], [
            'guard_name' => 'admin',
        ]);

        $role->givePermissionTo(Permission::all());
        $admin->assignRole($role);

        Store::createOrFirst([
            'email' => 'store@ps.com',
        ], [
            'name' => 'store',
            'password' => 'password',
        ]);
    }
}
