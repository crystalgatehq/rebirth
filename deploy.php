<?php

namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'psilocybin');

// Project repository
set('repository', 'git@github.com:wagura-maurice/psilocybin.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);
set('default_timeout', 150000);

// Shared files/dirs between deploys
add('shared_files', ['.env']);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);

// Hosts
host('206.189.120.35')
    ->user('deployer')
    ->port(22)
    ->identityFile('~/.ssh/id_rsa') // ssh on local machine that links to the deployer on vps
    ->set('deploy_path', '/var/www/html/{{application}}');

// Tasks
task('build', function () {
    run('cd {{release_path}} && build');
});

// Custom vendor installation task
task('deploy:vendors', function () {
    if (!commandExist('unzip')) {
        run('apt-get install -y unzip');
    }
    
    // Run composer install with optimized flags
    $composer = get('bin/composer');
    $composerCmd = "cd {{release_path}} && {$composer} install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader";
    
    // Run composer install and continue even if it fails
    run("$composerCmd || true");
    
    // Manually run the post-install scripts that might have failed
    run('cd {{release_path}} && {{bin/php}} artisan package:discover --no-ansi || true');
});

// Update the task sequence
task('release:application', function () {
    // Put the application into maintenance mode with default Laravel maintenance page
    run('{{bin/php}} {{release_path}}/artisan down --refresh=60');

    // Clear all caches
    run('{{bin/php}} {{release_path}}/artisan cache:clear');
    run('{{bin/php}} {{release_path}}/artisan config:clear');
    run('{{bin/php}} {{release_path}}/artisan route:clear');
    run('{{bin/php}} {{release_path}}/artisan view:clear');
    run('{{bin/php}} {{release_path}}/artisan event:clear');

    // Clear expired password reset tokens
    run('{{bin/php}} {{release_path}}/artisan auth:clear-resets');
    
    // Clear session files
    run('rm -f {{release_path}}/storage/framework/sessions/*');
    
    // Create storage link if it doesn't exist
    if (!test('[ -L {{release_path}}/public/storage ]')) {
        run('{{bin/php}} {{release_path}}/artisan storage:link');
    }

    // Run database migrations
    run('{{bin/php}} {{release_path}}/artisan migrate:fresh --seed --force --no-interaction');

    // Cache configuration
    run('{{bin/php}} {{release_path}}/artisan config:cache');
    run('{{bin/php}} {{release_path}}/artisan route:cache');
    run('{{bin/php}} {{release_path}}/artisan view:cache');
    run('{{bin/php}} {{release_path}}/artisan event:cache');

    // Handle queues - restart and clear
    run('{{bin/php}} {{release_path}}/artisan queue:restart');
    run('{{bin/php}} {{release_path}}/artisan queue:clear --force');
    
    // Clear failed jobs table if it exists
    run('{{bin/php}} {{release_path}}/artisan queue:flush');

    // General Optimizition of the application
    run('{{bin/php}} {{release_path}}/artisan optimize');

    // Run custom deployment commands
    run('{{bin/php}} {{release_path}}/artisan app:optimize --no-interaction');
    
    // Clear and warm the opcode cache
    if (str_contains(run('{{bin/php}} -m'), 'opcache')) {
        run('{{bin/php}} -r "opcache_reset();"');
    }

    // Set proper permissions
    run('chmod -R 755 {{release_path}}/bootstrap/cache');
    run('chmod -R 775 {{release_path}}/storage');

    // Bring the application back online
    run('{{bin/php}} {{release_path}}/artisan up');

    // Run web scraping task for Jumia products
    // run('{{bin/php}} {{release_path}}/artisan scrape:jumia-products');
})->once();

// Install composer dependencies with error handling
task('deploy:vendors', function () {
    if (commandExist('unzip') == false) {
        run('apt-get install -y unzip');
    }
    
    // Run composer install with optimized flags and continue on error
    run('cd {{release_path}} && {{bin/composer}} install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-suggest || true');
    
    // Manually run the post-install scripts that might have failed
    run('cd {{release_path}} && {{bin/php}} artisan package:discover --no-ansi || true');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database and other laravel tasks before symlink new release.
before('deploy:symlink', 'release:application');
before('deploy:symlink', 'deploy:vendors');
