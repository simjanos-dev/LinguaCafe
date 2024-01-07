## LinguaCafe
LinguaCafe is a software to help language learners read in foreign languages. 

[LinguaCafe overview](https://simjanos-dev.github.io/LinguaCafeHome/)

## Installation (Windows)
I couldn't install docker on windows yet. I will try to figure it out in the future, however if you manage to install and run docker on Windows, the commands should be the same, except you won't need "sudo chmod 777 ./* -R".

## Installation (Linux)
Step 1: Install docker desktop

Step 2: Download LinguaCafe code and run these commands from the directory of LinguaCafe.
> docker compose up -d
> sudo chmod 777 ./* -R  
> docker exec -ti linguacafe-webserver composer install  
> docker exec -ti linguacafe-webserver npm install  
> docker exec -ti linguacafe-webserver npm run prod  
> docker exec -ti linguacafe-webserver php artisan migrate  
> docker exec -ti linguacafe-webserver php artisan db:seed

Your server now should be running and accessible on http://localhost:9191.  

If you want to learn Japanese, it is highly recommended that you also import the JMDict files by following the steps below.

## JMDict import (recommended for Japanese)
Step 1: Download JMDict files.  

Download all the processed JMDict files from the [lastest release](https://github.com/simjanos-dev/LangApp/releases) on github. Download the .txt files, ignore the "Source code" files.

Step 2: Copy the files into the LinguaCafe/storage/app/dictionaries/ directory.  

Step 3: Login to LinguaCafe, and run these import scripts from your browser:

> http://localhost:9191/jmdict/import-jmdict  
> http://localhost:9191/jmdict/jmdict/import-kanji  
> http://localhost:9191/jmdict/import-radicals

## Attributions
LinguaCafe uses many public resources. I am very thankful for these projects and for all the people who were working on them. They helped me greatly to create LinguaCafe.


**Spacy tokenizer**  
License: MIT license

[Spacy website](https://spacy.io/)  
[Spacy github](https://github.com/explosion/spaCy/)  
[Spacy license](https://github.com/explosion/spaCy/blob/master/LICENSE)
&nbsp;

**Pykakasi**  
License: License: GNU General Public License 3

[Pykakasi website](https://codeberg.org/miurahr/pykakasi)  
[Pykakasi license](https://www.gnu.org/licenses/gpl-3.0.html)  
&nbsp;

**EbookLib**  
License: GNU Affero General Public License v3.0

[EbookLib github](https://github.com/aerkalov/ebooklib)  
[EbookLib license](https://github.com/aerkalov/ebooklib/blob/master/LICENSE.txt)
&nbsp;

**JMDict dictionary file**  
License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)  
[JMDict license information](https://www.edrdg.org/edrdg/licence.html)  
[JMDict license](https://creativecommons.org/licenses/by-sa/4.0/)
&nbsp;

**KANJIDIC2 kanji file**  
License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)  
[KANJIDIC2 license information](https://www.edrdg.org/edrdg/licence.html)  
[KANJIDIC2 license](https://creativecommons.org/licenses/by-sa/4.0/)
&nbsp;

**RADKFILE/KRADFILE**  
License: Creative Commons Attribution-ShareAlike 4.0 International

[JMDict Project website](https://www.edrdg.org/jmdict/j_jmdict.html)  
[KRADKFILE license information](https://www.edrdg.org/edrdg/licence.html)  
[KRADKFILE license](https://creativecommons.org/licenses/by-sa/4.0/)
&nbsp;

**DMAK kanji drawing library**  
License: MIT license

[DMAK github project](https://github.com/mbilbille/dmak/)  
[DMAK license](https://github.com/mbilbille/dmak/blob/master/LICENSE)
&nbsp;

**KanjiVG**  
License: Creative Commons Attribution-ShareAlike 3.0 Unported

[KanjiVG website](https://kanjivg.tagaini.net/)  
[KanjiVG github](https://github.com/KanjiVG/kanjivg)  
[KanjiVG license](https://creativecommons.org/licenses/by-sa/3.0/)
