#!/bin/bash

# Setup cron job for periodic Telegram notifications
echo "Setting up cron job for periodic Telegram notifications..."

# Add Laravel scheduler to crontab (runs every minute)
(crontab -l 2>/dev/null; echo "* * * * * cd /Users/tutran/Githubs/regmail && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo "✅ Cron job setup complete!"
echo "📅 Laravel scheduler will run every minute"
echo "📱 Periodic Telegram notifications will be sent every 4 hours"
echo ""
echo "To test manually:"
echo "  php artisan telegram:send-periodic --hours=4"
echo ""
echo "To check scheduled tasks:"
echo "  php artisan schedule:list"
