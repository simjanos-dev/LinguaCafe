#!/bin/sh

# Folders needed for persistence
folder_paths="
    ./storage/app/dictionaries
    ./storage/app/fonts
    ./storage/app/images/book_images
    ./storage/app/public
    ./storage/app/temp/dictionaries
    ./storage/framework/cache/data
    ./storage/framework/sessions
    ./storage/framework/testing
    ./storage/framework/views
    ./storage/logs
    ./storage/backup
"

# Ensure the folders exist
for folder_path in $folder_paths; do
    if [ ! -d "$folder_path" ]; then
        mkdir -p "$folder_path"
        echo "Folder created: $folder_path"
    else
        echo "Folder already exists: $folder_path"
    fi
done

retry_count=0

while [ $retry_count -lt 40 ] && ! php artisan migrate --force; do
    sleep 15
    retry_count=$((retry_count+1))
done

php artisan db:seed --force

exec "$@"
