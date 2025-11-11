<?php

namespace Database\Seeders\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeederReport
{
    public static function generate()
    {
        $output = "\n✅ Database seeding completed successfully!\n\n";
        $output .= str_repeat('=', 50) . "\n";
        $output .= "DATABASE SEEDING REPORT\n";
        $output .= str_repeat('=', 50) . "\n\n";

        // Table counts
        $output .= "=== TABLE COUNTS ===\n";
        $tables = ['users', 'roles', 'role_user', 'profiles'];
        $maxLength = max(array_map('strlen', $tables)) + 1;
        
        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            $output .= str_pad(ucfirst($table) . ':', $maxLength + 2) . $count . "\n";
        }
        $output .= "\n";

        // Roles distribution
        $output .= "=== ROLES DISTRIBUTION ===\n";
        $roles = DB::table('roles')
            ->leftJoin('role_user', 'roles.id', '=', 'role_user.role_id')
            ->select('roles.name', DB::raw('COUNT(role_user.user_id) as user_count'))
            ->groupBy('roles.id', 'roles.name')
            ->orderBy('roles.name')
            ->get();

        $maxRoleLength = $roles->max(fn($role) => strlen($role->name));
        
        foreach ($roles as $role) {
            $output .= sprintf(
                "%-{$maxRoleLength}s: %d %s\n",
                $role->name,
                $role->user_count,
                Str::plural('user', $role->user_count)
            );
        }

        $output .= "\n" . str_repeat('=', 50) . "\n";
        
        return $output;
    }
}
