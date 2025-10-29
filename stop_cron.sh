#!/bin/bash

# Stop cron job for periodic Telegram notifications
echo "Stopping cron job for periodic Telegram notifications..."

# Remove Laravel scheduler from crontab
crontab -l 2>/dev/null | grep -v "php artisan schedule:run" | crontab -

echo "✅ Cron job stopped successfully!"
echo "📅 Laravel scheduler has been removed from crontab"
echo ""
echo "To restart scheduler:"
echo "  ./setup_cron.sh"
echo ""
echo "To check current crontab:"
echo "  crontab -l"
