## Migrating from v0.8 to v0.9 on Mac

If you are a Mac user with Apple silicon, and you had to uncomment a line in the `docker-compose.yml` file before, please follow these instructions before you updateto v0.9:

Step 1: Comment the the line again at the end of the `docker-compose.yml` file :
```
volumes:
    - ./storage:/var/www/html/storage
networks:
    - linguacafe
# platform: linux/amd64
```

Step 2: Create a `.env` file in the linguacafe, and add the this line to it:

```
PLATFORM="linux/amd64"
```

This change will will simplify the update process, and prevent any possible conflict errors with the git pull command in the future.

## Migrating from v0.5 or v0.5.1 to higher
There was an issue again with docker, this time it is an easy fix. Please create a backup of your database, and run this command instead of the one provided in the general update guide:
```
git restore ./database/.gitkeep && git restore ./docker-compose.yml && git restore ./storage/app/dictionaries/.gitkeep && git restore ./storage/app/images/book_images/default.jpg && git pull && docker compose pull && docker compose up -d
```
## Migrating from v0.4 to v0.5
The difference since v0.4 is only the placement of the folders. We have decided to mount the whole `/storage` folder, so users won't have to create several folders. Due to an oversight with the v0.4 folder structure, you have to recover your book cover images, and change your folder structure. 

Run this command to recover your book cover images while the docker server is running:
```
docker cp linguacafe-webserver:/var/www/html/storage/app/images/book_images ./
```

Run this command from your old linguacafe folder to stop the servers:
```
docker compose down
```

The easiest way to migrate to the new structure is to clone the `deploy` branch from github, which contains all the necessary folder structure and files. Then you can copy your old database and book images there.  

Run this command to download and create the new folder structure. This will create a `linguacafe` folder:
```
git clone -b deploy https://github.com/simjanos-dev/LinguaCafe.git linguacafe
```
If you are an Apple silicon Mac user, uncomment the `platform: linux/amd64` line in the new `linguacafe/docker-compose.yml`.

Next copy your old database and book images to the new `linguacafe` folder. Copying the database will need root permissions. I advise you also make a copy of your database in case anything goes wrong.
```
/your/old/database          ->      /linguacafe/database
/your/old/book_images       ->      /linguacafe/storage/app/images/book_images
```

Run this command from the new `linguacafe` folder, to make sure all your files and folders have the necessary permission.
```
sudo chmod 777 -R ./
```
Finally, start the server with this command from the new `linguacafe` folder:
```
docker compose pull && docker compose up -d --force-recreate
```

Your server now should be running. Your old linguacafe folder can be deleted. 

I've somehow managed to change my files' ownership, and my server did not start up. I could not replicate the issue again, but stopping the server, running this command from the `linguacafe` and starting it again fixed it.
```
sudo chmod 777 -R ./ && sudo chown -R $USER:$USER ./database/ 
```
## Migrating from versions prior to v0.4
When editing the `docker-compose.yml` to add your storage paths, do these replacements to keep the files where they originally were created:

```
/your/linguacafe/dict/folder          ->  /path/to/this/repo/storage/app/dictionaries
/your/linguacafe/logs/folder          ->  /path/to/this/repo/storage/logs
/your/linguacafe/database/folder      ->  /path/to/this/repo/docker/mysql
```

It is also possible to move those three folders somewhere else with all their contents and use that path instead, in which case the cloned repo is not needed anymore and can be safely deleted after testing the migration was successful.