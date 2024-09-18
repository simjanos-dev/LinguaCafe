#!/bin/sh

while true; do
    sleep 60
    cd /var/www/html && php artisan schedule:run
done
