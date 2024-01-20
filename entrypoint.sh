#!/bin/sh

retry_count=0

while [ $retry_count -lt 40 ] && ! php artisan migrate; do
    sleep 15
    retry_count=$((retry_count+1))
done

php artisan db:seed

exec "$@"
