# Importing dictionaries

1. Download the dictionaries that you want to use from the provided links below.
2. Copy the dictionary files to your `linguacafe/storage/app/dictionaries` folder.
3. Go to the Admin -> Dictionaries page in LinguaCafe. Click on the `Import dictionary` button.
4. This dialog will list all your importable dictionaries that are found in your `dictionaries` folder. Click on the `import` button for the dictionary that you want to import.

After the import process is finished, your dictionary should be working.

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

You must enable DeepL translate for each language on the Admin -> Dictionaries page.

