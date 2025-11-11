#!/bin/bash

# Clear the console
clear

echo "🔧 Starting system cleanup..."

# Run maintenance commands
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
/usr/bin/php8.4 /usr/local/bin/composer dump-autoload

# Run migrations and seed the database
echo -e "\n🌱 Running database migrations and seeding..."
/usr/bin/php8.4 artisan migrate:fresh --seed --no-interaction

# Build frontend assets
echo -e "\n🔨 Building frontend assets..."
npm run build

# Bring the application back up
/usr/bin/php8.4 artisan up

echo -e "\n✨ Cleanup and deployment completed successfully!"