<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RolePermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ==========================================
        // Super Admin User
        // ==========================================
        User::firstOrCreate(
            ['email' => 'nepalramkumar@gmail.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'role'     => 'admin',
            ]
        );

        // ==========================================
        // Default Role Permissions
        // (Admin ले पछि Settings -> Permissions बाट यी जुनसुकै बेला बदल्न सक्छ)
        // ==========================================
        $defaultPermissions = [
            'manager' => [
                'overtime.entry',
                'overtime.entry.all',
                'overtime.verify',
                'events.manage',
                'reports.view',
                'petrol.bills.view',
                'repair.expenses.view',
            ],
            'employee' => [
                'overtime.entry',
                'petrol.bills.entry',
                'repair.expenses.entry',
            ],
            'account' => [
                'reports.view',
                'petrol.bills.manage',
                'petrol.bills.view',
                'petrol.months.manage',
                'repair.expenses.manage',
                'repair.expenses.view',
            ],
        ];

        foreach ($defaultPermissions as $role => $perms) {
            foreach ($perms as $perm) {
                RolePermission::firstOrCreate([
                    'role'       => $role,
                    'permission' => $perm,
                ]);
            }
        }
    }
}