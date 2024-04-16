# Importing dictionaries

1. Download the dictionaries that you want to use from the provided links below.
2. Copy the dictionary files to your `linguacafe/storage/app/dictionaries` folder.
3. Go to the **Admin** > **Dictionaries** page in LinguaCafe, and click on the **Import dictionary** button.
4. This dialog will list all your importable dictionaries that are found in your `dictionaries` folder. Click on the **Import** button for the dictionary that you want to import.

After the import process is finished, your dictionary should be available whenever you selet a word when reading.

## Dictionaries

| Dictionary | Languages | Download | Required Files | Comment |
| :--- | ---- | ---- | ---- | ---- |
| JMDict | Japanese | [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) | `jmdict_processed.txt`, `kanjidic2.xml`, `radical-strokes.txt`, `radicals.txt` | This dictionary contains kanji and radicals for the Japanese language. Some Japanese features do not work without importing this dictionary. |
| CC-CEDICT | Chinese |  [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) |  |  |
| Kengdic | Korean | [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) |  |  |
| Eurfa | Welsh | [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) |  |  |
|  Wiktionary | Chinese, Czech, Finnish, French, German, Italian, Japanese, Korean, Norwegian, Russian, Spanish, Ukrainian, Welsh | [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) |  |  |
| Dict.cc | Czech, Dutch, Finnish, French, German, Italian, Norwegian, Russian, Spanish, Swedish | [dict.cc](https://www1.dict.cc/translation_file_request.php?l=e) |  | This dictionary's license only allows personal use. |

## Custom dictionary

You can also import a custom dictionary file in the form of a `.csv` file.

## DeepL translate

DeepL is a machine translation service that lets you translate up to 500.000 characters/month for free and is supported by LinguaCafe. You can set your DeepL Translate API key in the admin API settings.

You must enable DeepL translate for each language on the **Admin** > **Dictionaries** page.

# Importing Vocabulary into LinguaCafe

If you have a list of words that you already know before you started using LinguaCafe, you can import them from a CSV file.

>[!NOTE]
>
> Changes after importing cannot be reverted, thus make sure you're importing only the words you want LinguaCafe to track.

To import words, go to the **Vocabulary** page, select the **Data** dropdown menu, and inside that click on the **Import** button. On the import dialog you can select your CSV file and a few options: 
- **Skip first row**. If enabled, LinguaCafe skips the first row which could be simply be the column names.
- **Only update**. If enabled, no new words will be added to the system. This allows you to only update fields for words that you have already encountered in LinguaCafe.

The CSV file can have these columns, in this order:

| Column Name | Required | Accepted Values | Comment |
| :--- | :--- | :--- | :--- |
| Word | Yes | Any word without any spaces. |  |
| Translation | No |  | Can be left empty. |
| Lemma | No |  | Can be left empty. |
| Reading | No |  | Can be left empty. |
| Lemma reading | No |  | Can be left empty. |
| Level | No | `new`, `ignored`, `learned`, `1`, `2`, `3`, `4`, `5`, `6`, `7` | Cannot be left empty. |

At least the first column must be present in the CSV file. Any further columns can be added to it in the order showed above. If a column is not provided, those fields will not be changed in the database. However if a column is provided, and it's left empty in a row, it will be overwritten in the database with an empty value.

After the import is complete, you will see a message about the number of created, updated and rejected words.

# Jellyfin
## Jellyfin configuration

You can use the network configuration from this example to connect Jellyfin's network with LinguaCafe. There are probably multiple ways to do it, the only requirement is that `linguacafe-webserver` should be able to reach Jellyfin's server to make API requests.

```
version: '3.5'
networks:
    linguacafe_linguacafe:
        external: true

services:
    jellyfin:
        image: jellyfin/jellyfin
        container_name: jellyfin
        user: 1000:1000
        volumes:
            - /path/to/config:/config
            - /path/to/cache:/cache
            - /path/to/media:/media:ro
        restart: 'unless-stopped'
        ports:
            - 8096:8096
        networks:
            - linguacafe_linguacafe
```

You must name your subtitle files in a way that Jellyfin will recognize as languages. These worked for me: 

```
Series Name - S01E01.ja.ass  
Series Name - S01E01.de.ass  
Movie name.es.ass  
```  

Language codes for subtitle filenames that Jellyfin recognizes:

| Language | Language Code |
| :--- | ---- |
| Chinese | `zh` |
| Czech | `cs` |
| Dutch | `nl` |
| Finnish | `fi` |
| French | `fr` |
| German | `de` |
| Italian | `it` |
| Japanese | `ja` |
| Korean | `ko` |
| Norwegian | `no` |
| Russian | `ru` |
| Spanish | `es` |
| Swedish | `sv` |
| Ukrainian | `uk` |
| Welsh | `cy` |

See [Jellyfin external file naming](https://jellyfin.org/docs/general/server/media/external-files/).

## Jellyfin API usage

1. Create an API key in Jellyfin. You can do this on the **Dashboard** > **API Keys** menu.
2. Set the created API key in LinguaCafe on to the **Admin** > **API** menu.
3. Set the Jellyfin host in LinguaCafe on to the **Admin** > **API** menu. If you used the pre-written configs, it should be the default http://jellyfin:8096.
4. Save the settings.

Now you can import subtitles from Jellyfin.
## Jellyfin troubleshooting

Possible error codes in browser console while importing from Jellyfin:

<details>
<summary><b>Error: unsupported language code: spa</b></summary>

This means that Jellyfin recognized the language of the subtitle, but it is not supported by LinguaCafe yet. If you find one of these, please open a GitHub Issue, this should be fixed. 

</details>

<details>
<summary><b>Error: unsupported language code: unrecognized by jellyfin: japaaaneseee</b></summary>

This means that Jellyfin did not recognize `japaaaneseee` as a language, and it can only be fixed by renaming the file following Jellyfin's naming conventions. 

If you have file naming issues and renamed a file, make sure you refresh metadata in Jellyfin before reloading LinguaCafe.

</details>

# Backup

>[!NOTE]
>
>This guide assumes you named your directory `linguacafe` during [installation](./installation.md). If you used a different name for your directory, simply replace `linguacafe` with it.

LinguaCafe stores your data in two directories:
- `linguacafe/storage` directory, which stores your files.
- `linguacafe/database` directory, which stores your database files.

Both must be saved to preserve all your LinguaCafe data.

To make a backup of your LinguaCafe instance, simply copy your whole `linguacafe` directory. On Linux you may need root privileges to copy the `database` folder, so please make sure that it was successful. Also make sure that the permissions are the same after restoring your data. You can reapply them by using the `chmod` command from the [installation guide](./installation.md).

>[!NOTE]
> **Backup your database regularly!** I highly recommend making regular backups, especially before upgrading LinguaCafe to a newer version. LinguaCafe is still in active development, and there is a high possibility of introducing a data corrupting bug.

## Exporting the LinguaCafe's database

Although copying the whole database folder works, you might also want to make a raw export of your database in order to remove the dependency on a functioning MySql docker container. This way you can have your database data in a single `.sql` file, e.g., `linguacafe-backup.sql`.

>[!NOTE] 
>
>If you run `docker ps -a`, then you should get all running Docker containers, among which there's `linguacafe-database` or a similarly named container, in which the database is running.
>

Run this command while your LinguaCafe server is running to export your database (If your database setup was changed manually, change names accordingly):

```
docker exec DATABASE-CONTAINER mysqldump --no-tablespaces -uUSERNAME -pPASSWORD DATABASE > ./linguacafe-backup.sql
```

where `DATABASE-CONTAINER`, `USERNAME`, and `PASSWORD` should be replaced with the names you used during [installation](./installation.md). If you kept the default names, then the command is simply:

```
docker exec linguacafe-database mysqldump --no-tablespaces -ulinguacafe -plinguacafe linguacafe > ./linguacafe-backup.sql
```

Now there should be a `linguacafe-backup.sql` under the `linguacafe` directory.

## Importing the LinguaCafe's database

You can import the database back with the following command:  

```
docker exec -i DATABASE-CONTAINER mysql -uUSERNAME -pPASSWORD DATABASE < ./linguacafe-backup.sql`
```

where `DATABASE-CONTAINER`, `USERNAME`, and `PASSWORD` should be replaced with the names you used during [installation](./installation.md). If you kept the default names, then the command is simply:

```
docker exec -i linguacafe-database mysql -ulinguacafe -plinguacafe linguacafe < ./linguacafe-backup.sql`
```

Now there should be a `linguacafe-backup.sql` under the `linguacafe` directory.




