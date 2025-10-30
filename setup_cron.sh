#!/bin/bash

# Setup cron job for periodic Telegram notifications
echo "Setting up cron job for periodic Telegram notifications..."

# Determine app directory (repo root) and PHP binary dynamically
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
APP_DIR="$SCRIPT_DIR"

# Try to detect git root if available
if command -v git >/dev/null 2>&1; then
  GIT_ROOT="$(git -C "$SCRIPT_DIR" rev-parse --show-toplevel 2>/dev/null)"
  if [ -n "$GIT_ROOT" ]; then
    APP_DIR="$GIT_ROOT"
  fi
fi

PHP_BIN="$(command -v php)"
if [ -z "$PHP_BIN" ]; then
  echo "❌ PHP binary not found in PATH. Aborting."
  exit 1
fi

CRON_LINE="* * * * * cd $APP_DIR && $PHP_BIN artisan schedule:run >> /dev/null 2>&1"

# Remove any existing schedule:run lines for this app dir, then add new one
TMP_CRON="$(mktemp)"
crontab -l 2>/dev/null | grep -v "artisan schedule:run" > "$TMP_CRON" || true
echo "$CRON_LINE" >> "$TMP_CRON"
crontab "$TMP_CRON"
rm -f "$TMP_CRON"

echo "✅ Cron job setup complete!"
echo "📅 Laravel scheduler will run every minute"
echo "📱 Periodic Telegram notifications will be sent every 2 hours"
echo ""
echo "To test manually:"
echo "  php artisan telegram:send-periodic --hours=2"
echo ""
echo "To check scheduled tasks:"
echo "  php artisan schedule:list"
