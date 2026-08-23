<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class UserProvisioningService
{
    /**
     * यो employee को login account छैन भने, नयाँ बनाउने र password-reset email पठाउने।
     */
    public function provisionFor(Employee $employee): ?User
    {
        // पहिले नै link भएको User छ भने, दोहोर्‍याएर नबनाउने
        $existingUser = User::where('employee_id', $employee->id)->first();
        if ($existingUser) {
            return $existingUser;
        }

        if (empty($employee->email)) {
            // Email नभएको employee को login auto-create गर्न मिल्दैन
            return null;
        }

        $user = User::create([
            'name'        => $employee->name,
            'email'       => $employee->email,
            'password'    => null,          // पछि आफैं reset गरेर बनाउने
            'role'        => 'employee',    // Default role — पछि Role Permissions बाट admin ले control गर्न सक्छ
            'employee_id' => $employee->id,
        ]);

        // Password बनाउने link सहितको email पठाउने
        Password::sendResetLink(['email' => $user->email]);

        return $user;
    }
}