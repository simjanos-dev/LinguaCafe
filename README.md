## LinguaCafe

## Installation
Step 1: Install docker desktop

Step 2: Create and start docker container
> docker compose up -d

Step 3: Run these commands
> docker exec -ti linguacafe-webserver composer install
> docker exec -ti linguacafe-webserver npm install
> docker exec -ti linguacafe-webserver npm run prod
> docker exec -ti linguacafe-webserver php artisan migrate
> docker exec -ti linguacafe-webserver php artisan db:seed

Your server now should be running and accessible on localhost:1234.


## Importing JMDict

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
