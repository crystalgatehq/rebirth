<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixDatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting database fix...');
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Fix role_user table (should have 55 records, one per user)
        $this->fixRoleUserTable();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Database fix completed.');
    }
    
    protected function fixRoleUserTable()
    {
        $this->command->info('Fixing role_user table...');
        
        // Clear the table
        DB::table('role_user')->truncate();
        
        // Get all users and assign each to one role based on email
        $users = User::all();
        
        foreach ($users as $user) {
            // Get the role name from the email (before @)
            $emailParts = explode('@', $user->email);
            $emailPrefix = $emailParts[0];
            
            // Handle special cases (like 'super.administrator' -> 'super administrator')
            $roleName = str_replace('.', ' ', $emailPrefix);
            $roleName = str_replace('_', ' ', $roleName);
            $roleName = ucwords($roleName);
            
            // Special case for 'maitre dhotel'
            $roleName = str_replace('maitre dhotel', 'maître d’hôtel', strtolower($roleName));
            $roleName = ucwords($roleName);
            
            // Find the role by name
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                // Try alternative naming (singular/plural)
                $role = Role::where('name', Str::singular($roleName))->first() ?: 
                        Role::where('name', Str::plural($roleName))->first();
            }
            
            if ($role) {
                // Assign the role to the user
                DB::table('role_user')->insert([
                    'role_id' => $role->id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->command->info("Assigned role '{$role->name}' to user '{$user->email}'");
            } else {
                $this->command->warn("No matching role found for user: {$user->email} (tried: {$roleName})");
            }
        }
        
        $count = DB::table('role_user')->count();
        $this->command->info("role_user table now has {$count} records (expected: 55)");
    }
    
}
