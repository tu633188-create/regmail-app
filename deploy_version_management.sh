#!/bin/bash

# Script to deploy App Version Management feature
# Run this after deploying new code with App Version Management

echo "🚀 Deploying App Version Management..."

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# 1. Run migrations
echo "📦 Running migrations..."
php artisan migrate --force

# 2. Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Update autoload
echo "🔄 Updating autoload..."
composer dump-autoload --no-dev --optimize

# 4. Cache for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Filament specific
echo "🎨 Updating Filament cache..."
php artisan filament:cache-components 2>/dev/null || true
php artisan filament:upgrade 2>/dev/null || true

echo ""
echo "✅ Done! App Version Management should now be available."
echo ""
echo "📋 Next steps:"
echo "   1. Check /admin/app-versions in your browser"
echo "   2. If tab still not showing, restart PHP-FPM:"
echo "      sudo systemctl restart php-fpm"
echo "      # or"
echo "      sudo systemctl restart php8.2-fpm"
echo ""

