## Development note
This got much more popular than I thought it would be. I expected maybe a dozen people who would be interested in it, and I was planning on developing it bit by bit. I will need some time to address every issue and add all the new requested features.

## LinguaCafe
LinguaCafe is a free self-hosted software that helps language learners learn vocabulary by reading. It provides a set of tools to read, look up unknown words and review them later on, as effortlessly as possible.

You can read about all the features of LinguaCafe in this [overview](https://simjanos-dev.github.io/LinguaCafeHome/).

Supported languages:
- German
- Japanese
- Norwegian
- Spanish

Experimental languages:
- Chinese
- Dutch
- Finnish
- French
- Italian
- Korean  

Experimental languages have been added recently and awaiting testing and community feedback to improve them. They may have problems, not work properly or have no dictionary sources provided.  

The text reader's font type is set to "notosans" in every language, which is a Japanese font type. It is likely that Chinese will need its own font type, and it is not displayed properly.

![Library](/GithubImages/LibraryCover.jpg)

![Reader](/GithubImages/TextReader.jpg)

![Review](/GithubImages/ReviewBackCard.jpg)

![Vocabulary](/GithubImages/VocabularySearch.jpg)

## Installation (Windows)
I couldn't install docker on windows yet. I will try to figure it out in the future, however if you manage to install and run docker on Windows, the commands should be the same, except you won't need `sudo chmod 777 ./* -R`.

## Installation (Linux)
1. Install docker desktop

2. Run these commands to clone and install LinguaCafe:
```sh
git clone https://github.com/simjanos-dev/LinguaCafe  
cd ./LinguaCafe  
sudo chmod 777 ./* -R  
docker compose up -d  
docker exec -ti linguacafe-webserver composer install  
docker exec -ti linguacafe-webserver npm install  
docker exec -ti linguacafe-webserver npm run prod  
docker exec -ti linguacafe-webserver php artisan migrate  
docker exec -ti linguacafe-webserver php artisan db:seed
```

Your server now should be running and accessible on http://localhost:9191.

If you want to learn Japanese, it is highly recommended that you also [import the JMDict files](#jmdict-import-recommended-for-japanese) by following the steps below.

## Updating to the latest version
Run these commands from the directory of LinguaCafe:
```sh
git pull
docker exec -ti linguacafe-webserver composer install  
docker exec -ti linguacafe-webserver npm install  
docker exec -ti linguacafe-webserver npm run prod  
docker exec -ti linguacafe-webserver php artisan migrate  
docker exec -ti linguacafe-webserver php artisan db:seed
```

## JMDict dictionary import (recommended for Japanese)
Step 1: Download JMDict files.  

Download all the processed JMDict files from the [latest release](https://github.com/simjanos-dev/LangApp/releases) on GitHub. Download the .txt and .xml files, ignore the "Source code" files.

2. Copy the files into the `LinguaCafe/storage/app/dictionaries/` directory.

3. Log in to LinguaCafe, and visit these import endpoints in your browser:

* http://localhost:9191/jmdict/import-jmdict
* http://localhost:9191/jmdict/import-kanji
* http://localhost:9191/jmdict/import-radicals

## Other dictionaries
You can find dictionaries [here](https://github.com/simjanos-dev/Dictionaries) for other languages, and instructions on how to import them.
I will add more sources in the future. If you know of any, I would appreciate it if you would send them to me.

## Jellyfin Docker config for new server

You do not need Jellyfin at all to use LinguaCafe, it is just an additional feature.

There is a pre-written configuration file for Jellyfin + LinguaCafe in [docker-compose-jellyfin.yml](docker-compose-jellyfin.yml) if you do not have a Jellyfin server set up yet. This sets up both LinguaCafe and Jellyfin for you.

Replace these paths with your own in docker-compose-jellyfin.yml:
> - /your/path/to/config:/config
> - /your/path/to/cache:/cache
> - /your/path/to/media:/media:ro


Then run the installation commands, but replace
```sh
docker compose up -d
```

with 
```sh
docker compose -f ./docker-compose-jellyfin.yml up -d
```

## Jellyfin Docker config for existing server
If you already have a Jellyfin server, you can use this example to connect Jellyfin's network with LinguaCafe.
There are probably multiple ways to do it, the only requirement is that linguacafe-webserver should be able to reach Jellyfin's server.

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

## Jellyfin API usage

1. Create an API key in Jellyfin. You can do this on the Jellyfin Dashboard -> API Keys menu.

2. Add the created API key in LinguaCafe in the Admin->API menu.

3. Add the Jellyfin host in LinguaCafe in the Admin->API menu. If you used one of the pre-written configs, it should be the default http://jellyfin:8096.

4. Save the settings. 

Now you should be able to read any external subtitles from any TV Show you play, in any of your Jellyfin clients on the Media player page. 
Unfortunately there is noticeable lag when you click on a timestamp to jump to a subtitle. I could only make it work without delays with the native "Jellyfin media player" client on my PC.

At this time it only works with TV Shows due to a bug which will be fixed soon. In the future there will be also an option to save the subtitles in the library like any text.

## Anki
Anki is supported, if your server and Anki run on the same PC (this will not be a requirement in the future) and have [AnkiConnect](https://ankiweb.net/shared/info/2055492159) plugin installed. 

## DeepL Translate
DeepL is a machine translation service that lets you translate up to 500,000 characters/month for free and is supported by LinguaCafe. You can set your DeepL Translate API key in the admin API settings. 

## Active development disclaimer
LinguaCafe is still in active development. There are a few missing features, and you might encounter some bugs while using the software. Please test it before you start actively using it, and make sure it is up to your expectations.

At this time only one user/server is supported.

I will soon add support for multiple users on one server, write detailed instructions for Windows install and create an easier installation experience if possible.

## Contact information
* [Discord invite](https://discord.gg/SuJqqA5d)
* Discord user: linguacafe_47757
* Reddit user: [/u/linguacafe](https://www.reddit.com/user/linguacafe)
* Subreddit: [/r/linguacafe](https://www.reddit.com/r/linguacafe/)

## Attributions
LinguaCafe uses many public resources. I am very thankful for these projects and for all the people who were working on them. They helped me greatly to create LinguaCafe.


### Spacy tokenizer
License: MIT license

[Spacy website](https://spacy.io/)<br/>
[Spacy github](https://github.com/explosion/spaCy/)<br/>
[Spacy license](https://github.com/explosion/spaCy/blob/master/LICENSE)<br/>


### Pykakasi
License: License: GNU General Public License 3

[Pykakasi website](https://codeberg.org/miurahr/pykakasi)<br/>
[Pykakasi license](https://www.gnu.org/licenses/gpl-3.0.html)<br/>


### EbookLib
License: GNU Affero General Public License v3.0

[EbookLib github](https://github.com/aerkalov/ebooklib)<br/>
[EbookLib license](https://github.com/aerkalov/ebooklib/blob/master/LICENSE.txt)<br/>


### JMDict dictionary file
License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)<br/>
[JMDict license information](https://www.edrdg.org/edrdg/licence.html)<br/>
[JMDict license](https://creativecommons.org/licenses/by-sa/4.0/)<br/>


### KANJIDIC2 kanji file
License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)<br/>
[KANJIDIC2 license information](https://www.edrdg.org/edrdg/licence.html)<br/>
[KANJIDIC2 license](https://creativecommons.org/licenses/by-sa/4.0/)<br/>


### RADKFILE/KRADFILE
License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)<br/>
[KRADKFILE license information](https://www.edrdg.org/edrdg/licence.html)<br/>
[KRADKFILE license](https://creativecommons.org/licenses/by-sa/4.0/)<br/>


### DMAK kanji drawing library
License: MIT license

[DMAK github project](https://github.com/mbilbille/dmak/)<br/>
[DMAK license](https://github.com/mbilbille/dmak/blob/master/LICENSE)<br/>


### KanjiVG
License: Creative Commons Attribution-ShareAlike 3.0 Unported

[KanjiVG website](https://kanjivg.tagaini.net/)<br/>
[KanjiVG github](https://github.com/KanjiVG/kanjivg)<br/>
[KanjiVG license](https://creativecommons.org/licenses/by-sa/3.0/)<br/>
