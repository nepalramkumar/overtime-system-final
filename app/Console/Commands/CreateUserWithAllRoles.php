<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUserWithAllRoles extends Command
{
    protected $signature = 'user:create-all-roles';
    protected $description = 'Create a test user and assign all roles and permissions';

    public function handle()
    {
        // १. युजर बनाउने (यहाँ 'admin' role दिइएको छ, तपाईंले चाहेको role राख्न सक्नुहुन्छ)
        $user = User::updateOrCreate(
            ['email' => 'allroles@example.com'],
            [
                'name' => 'Multi Role User',
                'password' => bcrypt('password'),
                'role' => 'admin' // तपाईंको users टेबलमा role स्तम्भ छ भने यो काम गर्छ
            ]
        );

        // २. तपाईंको Blade view मा भएका Roles र Permissions हरू
        $roles = ['admin', 'manager', 'staff']; 
        
        // तपाईंको Blade view मा $permissions को keys जो-जो छन् ती यहाँ राख्नुहोला
        // उदाहरणको लागि:
        $permissions = ['create', 'edit', 'delete', 'view']; 

        // ३. role_permissions टेबलमा सबै permission हरू हाल्ने (ताकि checkbox हरू tick देखापरोस्)
        foreach ($roles as $role) {
            foreach ($permissions as $permission) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role' => $role, 
                        'permission' => $permission
                    ],
                    [
                        'created_at' => now(), 
                        'updated_at' => now()
                    ]
                );
            }
        }

        $this->info('User created successfully and all permissions assigned!');
        $this->line('--------------------------------------------------');
        $this->line('Login Email: nepalramkumar@gmail.com');
        $this->line('Password:    Admin@123');
        $this->line('--------------------------------------------------');
    }
}