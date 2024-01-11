#!/bin/sh

php artisan migrate \
    && php artisan db:seed

exec "$@"
