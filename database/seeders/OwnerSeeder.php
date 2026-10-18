<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class OwnerSeeder extends Seeder
{
    /**
     * Seed the single Owner account used to log into the dashboard.
     * Credentials come from .env (OWNER_NAME / OWNER_EMAIL / OWNER_PASSWORD)
     * — there is no public registration in this single-user system.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        $owner = User::updateOrCreate(
            ['email' => config('bms.owner.email')],
            [
                'name' => config('bms.owner.name'),
                'password' => config('bms.owner.password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $owner->hasRole($role)) {
            $owner->assignRole($role);
        }
    }
}
