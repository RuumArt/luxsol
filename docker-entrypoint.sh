#!/usr/bin/env sh
php -f /var/www/scripts/databaseCheck.php
exec "$@"
