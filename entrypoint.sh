#!/bin/sh

npm run prod \
    && php artisan migrate \
    && php artisan db:seed

exec "$@"
