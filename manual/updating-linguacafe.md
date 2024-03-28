# Updating LinguaCafe

## V0.5.2 or below

Please use the migration guide provided [here](/migration.md).
## Above v0.5.2

> [!WARNING]  
> Before updating LinguaCafe, make a backup of the `linguacafe` folder (or the folder you used when setting up LinguaCafe for the first time). **If you don't back up your data, it might be lost forever.**

### Linux  and MacOs

In your terminal, run the following command:

```
git pull && docker compose pull && docker compose up -d --force-recreate
```

### Windows

You can run either the [Windows's LinguaCafe Installation script](/install_linguacafe.bat) or run the following commands one by one:

```
git pull
docker compose pull
docker compose up -d --force-recreate
```