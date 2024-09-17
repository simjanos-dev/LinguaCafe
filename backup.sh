#!/bin/sh

sleep 60
cd /var/www/html && php artisan schedule:run
