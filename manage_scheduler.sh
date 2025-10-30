#!/bin/bash

# Manage Laravel Scheduler for Telegram Notifications
echo "=== Laravel Scheduler Manager ==="
echo ""

case "$1" in
    "start"|"on"|"enable")
        echo "🚀 Starting scheduler..."
        ./setup_cron.sh
        ;;
    "stop"|"off"|"disable")
        echo "🛑 Stopping scheduler..."
        ./stop_cron.sh
        ;;
    "status"|"check")
        echo "📊 Checking scheduler status..."
        echo ""
        echo "Current crontab:"
        crontab -l 2>/dev/null | grep -E "(schedule:run|telegram:send-periodic)" || echo "No scheduler found in crontab"
        echo ""
        echo "Scheduled tasks:"
        php artisan schedule:list
        ;;
    "test")
        echo "🧪 Testing periodic notifications..."
        php artisan telegram:send-periodic --hours=2
        ;;
    "restart")
        echo "🔄 Restarting scheduler..."
        ./stop_cron.sh
        sleep 2
        ./setup_cron.sh
        ;;
    *)
        echo "Usage: $0 {start|stop|status|test|restart}"
        echo ""
        echo "Commands:"
        echo "  start    - Start the scheduler (enable cron job)"
        echo "  stop     - Stop the scheduler (disable cron job)"
        echo "  status   - Check scheduler status"
        echo "  test     - Test periodic notifications"
        echo "  restart  - Restart the scheduler"
        echo ""
        echo "Examples:"
        echo "  ./manage_scheduler.sh start"
        echo "  ./manage_scheduler.sh status"
        echo "  ./manage_scheduler.sh test"
        ;;
esac
