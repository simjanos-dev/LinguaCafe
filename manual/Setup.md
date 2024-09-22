# Setup
This section contains information related to hosting, setting up and maintaining your LinguaCafe server. There are some important steps to take after installation before you can use linguacafe, like installing additional languages and importing dictionaries.


# Installation

#### Step 1: Install docker desktop.

>[!IMPORTANT]
>
> On MacOS you might need actual Docker Desktop instead of just basic Docker, because it allows you to use Rosetta to run images without support for Arm64 like our Python image, which uses Spacy models that only work in Amd64.

#### Step 2: Create linguacafe folder and download the docker-compose.yml file.

Create a folder for linguacafe, and a storage subfolder. Then download the [docker-compose.yml](https://github.com/simjanos-dev/LinguaCafe/blob/main/docker-compose.yml) file, and place in inside your linguacafe folder. Your folder structure should look like this:
```
.
├── linguacafe
│   ├── storage
│   ├── docker-compose.yml
```

If you want to change the default MySQL database and user, you can create a `.env` file inside your linguacafe folder and add these lines to it before starting your servers for the first time:
```
DB_DATABASE="linguacafe"
DB_USERNAME="linguacafe"
DB_PASSWORD="linguacafe"
```

You can also use a remote MySql server. In this case, you must create the database itself before starting the server.
```
DB_HOST="linguacafe-database-host"
DB_PORT=3306
```

MacOs users with Apple silicon must also create a `.env` file, and add the following line:
```
PLATFORM="linux/amd64"
```

#### Step 3: Run this command to download the docker images and start your server:
```
docker compose up -d
```

**Windows:**

For Windows, you can download [this installation script](/install_linguacafe.bat) and run it instead of running any of the commands yourself. Since this is a .bat file, Windows defender will warn you about it being potentially a malware.

#### Step 4: Admin settings
Your server now should be running and accessible on http://localhost:9191. 

Although your server is set up and functional, please read the [user manual](https://github.com/simjanos-dev/LinguaCafe/wiki/2.-Setup), because there are a few additional steps before you can use linguacafe, like installing languages and importing dictionaries.

#### Install error troubleshooting
<details>
<summary><b>Mysql error while running the `docker compose up -d` command.</b></summary>

Some Apple silicon users have encountered error messages like these: 
```
[+] Pulling 1/3 on
✘ mysql Error context canceled 1.0s
⠏ webserver [⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀] Pulling 1.0s
⠏ python Pulling 1.0s
no matching manifest for linux/arm64/v8 in the manifest list entries
```

We do not know why, but pulling the images individually fixes this error.

Run these commands, then run `docker compose up -d` again:
```
docker pull --platform linux/arm64 ghcr.io/simjanos-dev/linguacafe-webserver:latest
docker pull --platform linux/amd64 ghcr.io/simjanos-dev/linguacafe-python-service:latest
```
</details>



# Beta

>[!NOTE]
>
>Please only participate in beta if you can set up LinguaCafe yourself, you can create a backup of your database, and if you don't mind encountering more issues than main version releases, including bugs that can corrupt your database. I'll always try to help if you encounter issues, but I have limited time, and it may take a few days before I can check out problems.

The beta docker image is a version of LinguaCafe that I use in my personal production environment. Previously I only tested it for a few days, but since I have less time nowadays, LinguaCafe has more users, and I am trying to develop it while maintaining a more stable version than did before, I will test updates for a bit longer.

To use the beta version of LinguaCafe, create a  `.env` file in your LinguaCafe directory if you don't already have one, and add this line to the end of it: `VERSION=beta`. After that run the update command of your operating system from the readme file. It will pull the latest beta docker image.

To see if a new beta version is released, you can just run the update command, or check the [GitHub actions](https://github.com/simjanos-dev/LinguaCafe/actions) page. I may change it up, but at this time there's no other way to track beta releases. Since the latest beta image will become the main version, there's no reason to move back to the main version, except if you want to stop using the beta when the next version comes out.

I created this docker image because I've seen people using unsupported features, and wanting to use the latest version as soon as possible. If you decide to use it, please **backup** before every update. Also keep in mind that reverting to older versions is not supported. 

If there ever will be extra steps necessary for beta users, I will display that in this section.

# Backup

>[!NOTE]
>
>This guide assumes you named your directory `linguacafe` during installation. If you used a different name for your directory, simply replace `linguacafe` with it.

LinguaCafe stores your data in two directories:
- `linguacafe/storage` directory, which stores your files.
- `linguacafe/database` directory, which stores your database files.

Both must be saved to preserve all your LinguaCafe data.

To make a backup of your LinguaCafe instance, simply copy your whole `linguacafe` directory. On Linux you may need root privileges to copy the `database` folder, so please make sure that it was successful. Also make sure that the permissions are the same after restoring your data. You can reapply them by using the `chmod` command from the installation guide.

To ensure that your installed language models works, you must restart your docker container after restoring a backup.

>[!NOTE]
> **Backup your database regularly!** I highly recommend making regular backups, especially before upgrading LinguaCafe to a newer version. LinguaCafe is still in active development, and there is a high possibility of introducing a data corrupting bug.

## Automatic backup
With the default settings LinguaCafe will create an automatic backup of your database every day at 23:59, and delete the oldest backup if you have more than 14. You can customize these values in the `docker-compose.yml` file, using cron syntax.

## Exporting the LinguaCafe's database

Although copying the whole database folder works, you might also want to make a raw export of your database in order to remove the dependency on a functioning MySql docker container. This way you can have your database data in a single `.sql` file, e.g., `linguacafe-backup.sql`.

>[!NOTE] 
>
>If you run `docker ps -a`, then you should get all running Docker containers, among which there's `linguacafe-webserver` or a similarly named container, in which the webserver is running.
>

Run this command while your LinguaCafe server is running to export your database:

```
docker exec -ti WEBSERVER-CONTAINER php artisan app:create-backup
```

where `WEBSERVER-CONTAINER` should be replaced with the name you used during installation. If you kept the default names, then the command is simply:

```
docker exec -ti linguacafe-webserver php artisan app:create-backup
```

You can find the created backup in your `linguacafe/storage/backup` folder.

## Importing the LinguaCafe's database

You can import the database back with the following command:  

```
docker exec -i DATABASE-CONTAINER mysql -uUSERNAME -pPASSWORD DATABASE < FILENAME`
```

where `DATABASE-CONTAINER`, `USERNAME`, `PASSWORD` and `FILENAME`, should be replaced with the names you used during installation. For example:

```
docker exec -i linguacafe-database mysql -ulinguacafe -plinguacafe linguacafe < ./storage/backup/linguacafe_2024_09_22_18_10_02.sql`
```

# Updating
When a new version of LinguaCafe is released, please create a backup, and read the **GitHub Release** notes and the main **GitHub Readme** file's update section before updating. If there is an important or a breaking change in the update, it will be noted in those places.

>[!CAUTION]
>
> LinguaCafe is still in active development, and it will change from month to month. Please make sure you backup your data regularly, and expect updates to have possible problems.

# Multiple users
LinguaCafe has added support for multiple users recently, however some features are not yet supported for a multi user setup. One of them is Anki. Highlighted words are being sent to Anki through the LinguaCafe server, and this setup does not make sense for multiple users. This will be changed in a future update, so multiple users can send their own cards to their own Anki software. 

User deletion is a missing feature currently.

LinguaCafe has some settings (mostly display related), which are stored locally in the browser. These settings are shared between multiple users, if they use the same device to access LinguaCafe. 

These limitations will be fixed in a future update.

# Languages

In LinguaCafe all the data are separated by the selected language. This means that any action you take in one language will not affect the data in other languages, so the first thing you should do in LinguaCafe is select your target language. You can change your selected language by clicking on the flag in the bottom left corner.

When you import text, LinguaCafe does:
- **Lemma generation:** When you import a text into LinguaCafe, the text processor will automatically assign dictionary form to words for supported languages. For example, it will assign the lemma `to work` to words such as `worked`.
- **Gender tagging:** In gendered and supported languages, LinguaCafe will prepend nouns with additional information based on the words' gender. 

## Installing languages
Some languages are not packaged in the docker image. These languages can be installed on the **Admin** > **Languages** page. Installing a language can take several minutes, and requires internet connection. Installed languages are being saved into the **storage** directory. 

Uninstalling languages are only possible by uninstalling all the installed languages.

## Supported Languages

LinguaCafe supports the following languages:

| Flag                                              | Language  | DeepL   | Lemma generation | Gender tagging      | Dictionaries          |
|:-------------------------------------------------:|:---------:|:-------:|:----------------:|:-------------------:|-----------------------|
| <img src='images/flags/chinese.png' width='25'>   | Chinese   | &check; |                  |                     | wiktionary, cc-cedict |
| <img src='images/flags/croatian.png' width='25'>  | Croatian  |         | &check;          |                     | dict cc               |
| <img src='images/flags/czech.png' width='25'>     | Czech     | &check; |                  |                     | wiktionary, dict cc   |
| <img src='images/flags/danish.png' width='25'>    | Danish    | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/dutch.png' width='25'>     | Dutch     | &check; | &check;          |                     | dict cc               |
| <img src='images/flags/english.png' width='25'>   | English   | &check; | &check;          |                     | dict cc               |
| <img src='images/flags/finnish.png' width='25'>   | Finnish   | &check; | inaccurate       |                     | wiktionary, dict cc   |
| <img src='images/flags/french.png' width='25'>    | French    | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/german.png' width='25'>    | German    | &check; | &check;          | &check;             | wiktionary, dict cc   |
| <img src='images/flags/greek.png' width='25'>     | Greek     | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/italian.png' width='25'>   | Italian   | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/japanese.png' width='25'>  | Japanese  | &check; | &check;          |                     | jmdict, wiktionary    |
| <img src='images/flags/korean.png' width='25'>    | Korean    | &check; | &check;          |                     | wiktionary, kengdic   |
| <img src='images/flags/latin.png' width='25'>     | Latin     |         |                  |                     | wiktionary            |
| <img src='images/flags/macedonian.png' width='25'>| Macedonian|         | &check;          |                     | wiktionary            |
| <img src='images/flags/norwegian.png' width='25'> | Norwegian | &check; | &check;          | &check;             | wiktionary, dict cc   |
| <img src='images/flags/polish.png' width='25'>    | Polish    | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/portuguese.png' width='25'>| Portuguese| &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/romanian.png' width='25'>  | Romanian  | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/russian.png' width='25'>   | Russian   | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/slovenian.png' width='25'> | Slovenian | &check; | &check;          |                     | wiktionary            |
| <img src='images/flags/spanish.png' width='25'>   | Spanish   | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/swedish.png' width='25'>   | Swedish   | &check; | &check;          |                     | dict cc               |
| <img src='images/flags/thai.png' width='25'>      | Thai      |         |                  |                     | wiktionary            |
| <img src='images/flags/turkish.png' width='25'>   | Turkish   | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='images/flags/ukrainian.png' width='25'> | Ukrainian | &check; |                  |                     | wiktionary            |
| <img src='images/flags/welsh.png' width='25'>     | Welsh     |         |                  |                     | wiktionary, eurfa     |

> [!NOTE]  
> For Chinese only Mandarin language is supported with simplified Chinese characters.


# Importing dictionaries

1. Download the dictionaries that you want to use from the provided links below.
2. Go to the **Admin** > **Dictionaries** page in LinguaCafe, and click on the **Add dictionary** button.
3. Select the **Supported dictionary file from the user manual** option, then upload the downloaded file.
4. Check if the detected dictionary's data is correct, then click on the **Import** button.

After the import process is finished, your dictionary should be available whenever you select a word while reading.

>[!CAUTION]
>
> Do not rename any dictionary files. For some dictionaries the filename is used to identify them.

## Dictionaries

| Dictionary | Languages | Download | Comment |
| :--- | ---- | ---- | ---- |
| JMDict | Japanese | [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) | This dictionary contains kanji and radicals for the Japanese language. Some Japanese features do not work without importing this dictionary. |
| CC-CEDICT | Chinese |  [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) |  |
| Kengdic | Korean | [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) |  |
| Eurfa | Welsh | [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) |  |
|  Wiktionary | Chinese, Czech, Finnish, French, German, Italian, Japanese, Korean, Norwegian, Russian, Spanish, Ukrainian, Welsh | [GitHub release](https://github.com/simjanos-dev/LinguaCafe/releases/tag/dictionaries) |  |
| Dict.cc | Czech, Dutch, Finnish, French, German, Italian, Norwegian, Russian, Spanish, Swedish | [dict.cc](https://www1.dict.cc/translation_file_request.php?l=e) |  | This dictionary's license only allows personal use. |

>[!NOTE]
>
> To import JMDict you must download all 4 of these files: `jmdict_processed.txt`, `kanjidic2.xml`, `radical-strokes.txt`, `radicals.txt`


## Custom dictionary

You can also import a custom dictionary file in the form of a `.csv` file.

## DeepL translate

DeepL is a machine translation service that lets you translate up to 500.000 characters/month for free and is supported by LinguaCafe. To access the Deepl API, you'll need to create an [API key](https://support.deepl.com/hc/en-us/articles/360020695820-API-Key-for-DeepL-s-API), add it in **Admin** > **API** > **DeepL**, and enable the DeepL dictionary.

After that, go to the **Admin** -> **Dictionaries** page, and click the **Add dictionary button**, and select the DeepL dictionary option. Here you can select what language do you want DeepL to translate to. You can add multiple DeepL dictionaries for the same language, if you want it to translate to multiple languages. 

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

# Anki

Currently Anki is supported if your server and Anki run on the same PC and have the [AnkiConnect](https://ankiweb.net/shared/info/2055492159) plugin installed.

To set up an Anki's connection, head over to **Admin** > **API** > **Anki**.

> [!NOTE]
>
>Future versions of LinguaCafe won't have this requirement.

# Jellyfin

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
| Croatian | `hr` |
| Czech | `cs` |
| Danish | `da` |
| Dutch | `nl` |
| Finnish | `fi` |
| French | `fr` |
| German | `de` |
| Italian | `it` |
| Japanese | `ja` |
| Korean | `ko` |
| Lithuanian | `lt` |
| Macedonian | `mk` |
| Norwegian | `no` |
| Polish | `pl` |
| Portuguese | `pt` |
| Romanian | `ro` |
| Russian | `ru` |
| Slovenian | `sl` |
| Spanish | `es` |
| Swedish | `sv` |
| Thai | `th` |
| Turkish | `tr` |
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
