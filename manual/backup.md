# Backup

>[!IMPORTANT]
>
>This guide assumes you named your directory `linguacafe` during [installation](./installation.md). If you used a different name for your directory, simply replace `linguacafe` with it.

LinguaCafe stores your data in two directories:
- `linguacafe/storage` directory, which stores your files.
- `linguacafe/database` directory, which stores your database files.

Both must be saved to preserve all your LinguaCafe data.

To make a backup of your LinguaCafe instance, simply copy your whole `linguacafe` directory. On Linux you may need root privileges to copy the `database` folder, so please make sure that it was successful. Also make sure that the permissions are the same after restoring your data. You can reapply them by using the `chmod` command from the [installation guide](./installation.md).

>[!WARNING]
> **Backup your database regularly!** I highly recommend making regular backups, especially before upgrading LinguaCafe to a newer version. LinguaCafe is still in active development, and there is a higher possibility of introducing a data corrupting bug.

## Exporting the LinguaCafe's database

Although copying the whole database folder works, you might also want to make a raw export of your database in order to remove the dependency on a functioning MySql docker container. This way you can have your database data in a single `.sql` file, e.g., `linguacafe-backup.sql`.

Run this command while your LinguaCafe server is running to export your database (If your database setup was changed manually, change names accordingly):

>[!TIP] 
>
>If you run `docker ps -a`, then you should get all running Docker containers, among which there's `linguacafe-database` or a similarly named container, in which the database is running.
>

```
docker exec DATABASE-CONTAINER mysqldump --no-tablespaces -uUSERNAME -pPASSWORD DATABASE > ./linguacafe-backup.sql
```

where `DATABASE-CONTAINER`, `USERNAME`, and `PASSWORD` should be replaced with the names you used during [installation](./installation.md). If you kept the default names, then the command is simply:

```
docker exec linguacafe-database mysqldump --no-tablespaces -ulinguacafe -plinguacafe linguacafe > ./linguacafe-backup.sql
```

Now there should be a `lingucafe-backup.sql` under the `linguacafe` directory.
## Importing the LinguaCafe's database

You can import it back with the following command:  

```
docker exec -i DATABASE-CONTAINER mysql -uUSERNAME -pPASSWORD DATABASE < ./linguacafe-backup.sql`
```

where `DATABASE-CONTAINER`, `USERNAME`, and `PASSWORD` should be replaced with the names you used during [installation](./installation.md). If you kept the default names, then the command is simply:

```
docker exec -i linguacafe-database mysql -ulinguacafe -plinguacafe linguacafe < ./linguacafe-backup.sql`
```

Now there should be a `lingucafe-backup.sql` under the `linguacafe` directory.