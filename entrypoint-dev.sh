#!/bin/sh

composer install \
    && npm install \
    && npm run prod

php artisan migrate \
    && php artisan db:seed

exec "$@"
