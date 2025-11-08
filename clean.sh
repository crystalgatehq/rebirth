/usr/bin/php8.4 artisan down
/usr/bin/php8.4 artisan cache:clear
/usr/bin/php8.4 artisan route:clear
/usr/bin/php8.4 artisan route:cache
/usr/bin/php8.4 artisan config:clear
/usr/bin/php8.4 artisan config:cache
/usr/bin/php8.4 artisan view:clear
/usr/bin/php8.4 artisan optimize
/usr/bin/php8.4 artisan storage:unlink
/usr/bin/php8.4 artisan storage:link
/usr/bin/php8.4 artisan up
/usr/bin/php8.4 /usr/local/bin/composer dump-autoload
# Run migrations and seed the database
/usr/bin/php8.4 artisan migrate:fresh --seed

# Generate database report
echo -e "\n\033[1;34m=== DATABASE SEEDING REPORT ===\033[0m"

# Table counts
echo -e "\n\033[1;32m=== TABLE COUNTS ===\033[0m"
/usr/bin/php8.4 artisan tinker --execute="
    echo 'Users:      ' . DB::table('users')->count() . '\n';
    echo 'Roles:      ' . DB::table('roles')->count() . '\n';
    echo 'Teams:      ' . DB::table('teams')->count() . '\n';
    echo 'Team_User:  ' . DB::table('team_user')->count() . '\n';
    echo 'Role_User:  ' . DB::table('role_user')->count() . '\n';
    echo 'Profiles:   ' . DB::table('profiles')->count() . '\n';
    echo 'Biometrics: ' . DB::table('biometrics')->count() . '\n';
"

# Team roles
echo -e "\n\033[1;32m=== TEAM ROLES ===\033[0m"
/usr/bin/php8.4 artisan tinker --execute="
    DB::table('team_user')
        ->select('role', DB::raw('count(*) as count'))
        ->groupBy('role')
        ->get()
        ->each(function(\$item) {
            echo ucfirst(\$item->role) . 's: ' . \$item->count . '\n';
        });
"

# Roles distribution
echo -e "\n\033[1;32m=== ROLES DISTRIBUTION (first 20) ===\033[0m"
/usr/bin/php8.4 artisan tinker --execute="
    DB::table('roles')
        ->leftJoin('role_user', 'roles.id', '=', 'role_user.role_id')
        ->select('roles.name', DB::raw('count(role_user.user_id) as user_count'))
        ->groupBy('roles.id', 'roles.name')
        ->orderBy('user_count', 'desc')
        ->limit(20)
        ->get()
        ->each(function(\$item) {
            echo '\033[0;36m' . str_pad(\$item->name . ':', 30) . '\033[0m' . \$item->user_count . ' users\n';
        });
    
    echo '\n... and ' . (DB::table('roles')->count() - 20) . ' more roles\n';
"

echo -e "\n\033[1;34m"`printf '=%.0s' {1..50}`"\033[0m\n"

# Build frontend assets
npm run build