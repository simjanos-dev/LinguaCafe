## Updating JMDict files

With each release, JMDict files will be updated. However if you would like to update it, here are the instructions how to do it yourself. This code was meant to be temporary, but i had no time to rework it yet. It will be made easier to do in the future.

**Step 1:**

Download the current jmdict file from [here](http://ftp.edrdg.org/pub/Nihongo/JMdict_e.gz). Rename it to "JMdict_e.xml" and copy the file to "/storage/app/dictionaries/JMdict_e.xml".

**Step 2:**

Download the current kradfiles from [here](http://ftp.edrdg.org/pub/Nihongo/kradzip.zip). Copy the contents of "kradfile2" to the end of "kradfile" without the commented lines. Convert it to UTF-8 and rename it to "radicals.txt" and save the file to "/storage/app/dictionaries/radicals.txt".

**Step 3:**

Download the current kanjidic file from [here](http://www.edrdg.org/kanjidic/kanjidic2.xml.gz). Unpack the file and copy it to "/storage/app/dictionaries/kanjidic2.xml".

**Step 4:**

Open this url and run this php script. This will create a jmdict.txt file with filtered data.

```
http://[LinguaCafeHost]/jmdict/xml-to-text
```

**Step 5:**

Run this command. This will create a "jmdict_processed.txt" file that contains conjugations. It might take a while for it to finish.

```
docker exec -w /var/www/html/tools/jmdict_conjugation -ti langapp-python-service /var/www/html/tools/jmdict_conjugation/jmdict_conjugation.py
```

**Step 6:**

Run these scripts. These will import radicals, kanjis and words from the downloaded and generated files into the database. These can take several minutes.

```
http://[LinguaCafeHost]/jmdict/import-jmdict
http://[LinguaCafeHost]/jmdict/jmdict/import-kanji
http://[LinguaCafeHost]/jmdict/import-radicals
```