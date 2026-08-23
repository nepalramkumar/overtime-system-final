<?php

namespace App\Http\Middleware;

use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            abort(403, 'माफ गर्नुहोस्, यो page हेर्न login चाहिन्छ।');
        }

        $role = auth()->user()->role;

        // Admin लाई जहिले पनि full access
        if ($role === 'admin') {
            return $next($request);
        }

        // यदि मल्टिपल पर्मिसनहरू कमाले छुट्टაएर आएका छन् भने तिनलाई एरेमा रूपान्तरण गर्ने
        $allPermissions = [];
        foreach ($permissions as $permission) {
            $allPermissions = array_merge($allPermissions, explode(',', $permission));
        }

        foreach ($allPermissions as $perm) {
            $cleanPerm = trim($perm);
            $hasPermission = RolePermission::where('role', $role)
                                        ->where('permission', $cleanPerm)
                                        ->exists();
            if ($hasPermission) {
                return $next($request);
            }
        }

        abort(403, 'माफ गर्नुहोस्, यो feature प्रयोग गर्ने अधिकार तपाईंको role लाई छैन।');
    }
}