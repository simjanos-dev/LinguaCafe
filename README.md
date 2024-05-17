## LinguaCafe

![GitHub Release](https://img.shields.io/github/v/release/simjanos-dev/LinguaCafe?label=Release&logo=buymeacoffee&logoColor=white&color=%23b58873) ![Static Badge](https://img.shields.io/badge/Languages-27-b58873?logo=readme&logoColor=white) [![Discord](https://img.shields.io/discord/1193631644662386788?logo=discord&logoColor=white&label=Discord&color=%235460ce&link=https%3A%2F%2Fdiscord.gg%2FwZYZYrdaeP)](https://discord.gg/wZYZYrdaeP) ![Static Badge](https://img.shields.io/badge/Jellyfin-API-%23983883?logo=jellyfin&logoColor=white) ![Static Badge](https://img.shields.io/badge/DeepL-API-%23983883?logo=deepl&logoColor=white) ![Static Badge](https://img.shields.io/badge/Anki-API-%23983883?logo=data:image/svg%2bxml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiBoZWlnaHQ9IjQ4cHQiIHZpZXdCb3g9IjAgMCA0OCA0OCIgd2lkdGg9IjQ4cHQiPjxsaW5lYXJHcmFkaWVudCBpZD0iYSIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCg0OS4wNzcgMCAwIDQ5LjY1MSAtMS4wMTcgLS45MDkpIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAiIHgyPSIuOTA3NDg4IiB5MT0iLjUiIHkyPSIuOTIwMDc4IiBmaWxsPSIjZmZmZmZmIj48c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiNmZmZmZmYiIGZpbGw9IiNmZmZmZmYiLz48c3RvcCBvZmZzZXQ9Ii44Mzg4ODM5NzkzOCIgc3RvcC1jb2xvcj0iI2ZmZmZmZiIgZmlsbD0iI2ZmZmZmZiIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZD0ibTIwLjUwOSAxMi42OTdjLS4yNzggMS4yNTktMS41MjggMi4wNjEtMi43OSAxLjc5LTEuMjYzLS4yNzEtMi4wNjItMS41MTktMS43ODUtMi43ODRsMS40ODQtNi44OTNjLjY3OS0zLjE1NiAzLjA4NS0zLjg2NiA1LjM2OS0xLjU4NGw4LjE2OSA4LjE2MSAxMS4yNzMtLjkxYzMuMjE4LS4yNiA0LjY1NSAxLjg3MiAzLjIwNyA0Ljc1N2wtNC45MzQgOS44MjkgMy44MjYgOS45MTVjMS4xNjIgMy4wMTItLjQ4MSA1LjAzOC0zLjY2OCA0LjUyMWwtMTEuMzE4LTEuODM2LTguOTM5IDcuMzZjLTIuNDkyIDIuMDUzLTQuODI5IDEuMTE3LTUuMjE2LTIuMDg4bC0xLjMtMTAuNzcxLTkuOTctNi4zNDZjLTIuNzIzLTEuNzM0LTIuNTIxLTQuMTYzLjQ1Mi01LjQyMmw4LjE4NC0zLjQ2NGMxLjE4OS0uNTAzIDIuNTYyLjA1MiAzLjA2NiAxLjI0LjUwMyAxLjE4OC0uMDUyIDIuNTYyLTEuMjQxIDMuMDY2bC01LjM2OSAyLjI4MSA5LjI1NCA1Ljg4NCAxLjI0IDEwLjI5NiA4LjUwNS02Ljk5NiAxMS4xMjYgMS44MDEtMy43NDQtOS42OTggNC43NjEtOS40NjUtMTAuOTczLjg4OS03Ljc2OC03Ljc1NnoiIGZpbGw9IiNmZmZmZmYiLz48L3N2Zz4=)

LinguaCafe is a free self-hosted software that helps language learners acquire vocabulary by reading. It provides a set of tools to read, look up unknown words and review them later as effortlessly as possible.

You can read about all the features of LinguaCafe on the [overview](https://simjanos-dev.github.io/LinguaCafeHome/) GitHub Page, and on the [user manual](https://github.com/simjanos-dev/LinguaCafe/wiki/1.-Home) GitHub Wiki page.

&nbsp;
&nbsp;

![Library](/GithubImages/LibraryCover.jpg)

![Reader](/GithubImages/TextReader.jpg)

![Review](/GithubImages/ReviewBackCard.jpg)

![Vocabulary](/GithubImages/VocabularySearch.jpg)

## Language support

**Supported languages:** Chinese, Croatian, Czech, Danish, Dutch, English, Finnish, French, German, Greek, Italian, Japanese, Korean, Latin, Macedonian, Norwegian, Polish, Portuguese, Romanian, Russian, Slovenian, Spanish, Swedish, Thai, Turkish, Ukrainian, Welsh.

You can find a detailed list about what level of support different languages have on the [GitHub Wiki](https://github.com/simjanos-dev/LinguaCafe/wiki) page.

## Supported platforms:
- x64, which includes most desktop computers made in the last decade.
- Mac users with Apple silicon must perform an additional step, see the 'Installation' section for details

Other Armv8 devices such as Raspberry Pis 3 and newer do not work at the moment.

## Memory usage
LinguaCafe uses RAM based on how many and which languages do you use. If you use every language, the RAM usage can be over 2GB.

## Installation

#### Step 1: Install docker desktop and git.

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

## Updating to the latest version 

Please **backup** linguacafe before updating, otherwise you can lose your data if anything goes wrong. You can read more about backups in the [user manual](https://github.com/simjanos-dev/LinguaCafe/wiki/2.-Setup#backup).

If you are below v0.9, please use the migration guide provided [here](/migration.md) instead of this command.

If you are below v0.12 and using Linux or MacOS, please run this command from your linguacafe directory (this won't be neccessary anymore in the future):
```
sudo chmod -R 777 ./storage
```

Run these commands to update and start your server:
```
docker compose pull
docker compose up -d
```

##### Windows
On Windows, you can run again [the installation script](/install_linguacafe.bat) to update to the latest version, or run the commands separately:

## Active development disclaimer

>[!NOTE]
> LinguaCafe is still in active development, you might encounter some bugs while using the software. Please test it before you start actively using it, and make sure it is up to your expectations.
> At this time only one user/server is supported.

## Contact information
[Discord invite](https://discord.gg/wZYZYrdaeP)

Discord user: linguacafe_47757

Subreddit: /r/linguacafe

E-mail: simjanos.dev@gmail.com

## Attributions
LinguaCafe uses many public resources. I am very thankful for these projects and for all the people who were working on them. They helped me greatly to create LinguaCafe.

**Spacy tokenizer**

License: MIT license

[Spacy website](https://spacy.io/)

[Spacy github](https://github.com/explosion/spaCy/)

[Spacy license](https://github.com/explosion/spaCy/blob/master/LICENSE)

**Pykakasi**

License: License: GNU General Public License 3

[Pykakasi website](https://codeberg.org/miurahr/pykakasi)

[Pykakasi license](https://www.gnu.org/licenses/gpl-3.0.html)

**Pinyin**

License: MIT license

[Pinyin website](https://pinyin.lxyu.net/)

[Pinyin github](https://github.com/lxyu/pinyin)

[Pinyin license](https://github.com/lxyu/pinyin?tab=MIT-1-ov-file#readme)

**Newspaper3k**

License: MIT, Apache-2.0

[Newspaper3k github](https://github.com/codelucas/newspaper)

[Newspaper3k license](https://github.com/codelucas/newspaper/blob/master/LICENSE)

**EbookLib**

License: GNU Affero General Public License v3.0

[EbookLib github](https://github.com/aerkalov/ebooklib)

[EbookLib license](https://github.com/aerkalov/ebooklib/blob/master/LICENSE.txt)

**JMDict dictionary file**

License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)

[JMDict license information](https://www.edrdg.org/edrdg/licence.html)

[JMDict license](https://creativecommons.org/licenses/by-sa/4.0/)

**KANJIDIC2 kanji file**

License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)

[KANJIDIC2 license information](https://www.edrdg.org/edrdg/licence.html)

[KANJIDIC2 license](https://creativecommons.org/licenses/by-sa/4.0/)
&nbsp;

**CC-CEDICT dictionary file**  
License: Creative Commons Attribution-Share Alike 3.0 License

[CC-CEDICT website](https://cc-cedict.org/wiki/)
[CC-CEDICT license](https://creativecommons.org/licenses/by-sa/3.0/)
&nbsp;

**HanDeDict dictionary file**  
License: Creative Commons Attribution-ShareAlike 2.0 Germany License

[HanDeDict website](http://www.handedict.de/)
[HanDeDict license](https://creativecommons.org/licenses/by-sa/2.0/de/)
&nbsp;

**Kengdic dictionary file**  
License: GNU Library General Public License, version 2.0

[Kengdic github](https://github.com/garfieldnate/kengdic)
[Kengdic license](https://www.gnu.org/licenses/old-licenses/lgpl-2.0.en.html)
&nbsp;

**Eurfa dictionary file**  
License: The GNU General Public License 3

[Eurfa download website](https://www.kaggle.com/datasets/rtatman/eurfa-welsh-dictionary?resource=download)
[Eurfa bitbucket](https://bitbucket.org/donnek/eurfa/src/master/)
[Eurfa creator's website](http://kevindonnelly.org.uk/)
[Eurfa license](https://bitbucket.org/donnek/eurfa/src/master/gpl.txt)
&nbsp;

**Wiktionary**

License: Creative Commons Attribution-ShareAlike 3.0 Unported License

[Wiktionary website](https://en.wiktionary.org/wiki/Wiktionary:Main_Page)

[Wiktionary license](https://en.wiktionary.org/wiki/Wiktionary:Text_of_Creative_Commons_Attribution-ShareAlike_3.0_Unported_License)

The specific wiktionary files that LinguaCafe uses have been downloaded from [this](https://github.com/Vuizur/Wiktionary-Dictionaries) GitHub repository.

**<span>Dict</span>.cc**

LinguaCafe has no <span>dict</span>.cc dictionaries packaged in the software. It only provides a link to the <span>dict</span>.cc website.

[Dict.cc license](https://www1.dict.cc/translation_file_request.php?l=e)

**RADKFILE/KRADFILE**

License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)

[KRADKFILE license information](https://www.edrdg.org/edrdg/licence.html)

[KRADKFILE license](https://creativecommons.org/licenses/by-sa/4.0/)

**DMAK kanji drawing library**

License: MIT license

[DMAK github project](https://github.com/mbilbille/dmak/)

[DMAK license](https://github.com/mbilbille/dmak/blob/master/LICENSE)

**KanjiVG**
License: Creative Commons Attribution-ShareAlike 3.0 Unported

[KanjiVG website](https://kanjivg.tagaini.net/)

[KanjiVG github](https://github.com/KanjiVG/kanjivg)

[KanjiVG license](https://creativecommons.org/licenses/by-sa/3.0/)
