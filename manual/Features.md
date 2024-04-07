# Languages

In LinguaCafe all the data are separated by the selected language. This means that any action you take in one language will not affect the data in other languages, so the first thing you should do in LinguaCafe is select your target language. You can change your selected language by clicking on the flag in the bottom left corner.

When you import text, LinguaCafe does:
- **Lemma generation:** When you import a text into LinguaCafe, the text processor will automatically assign dictionary form to words for supported languages. For example, it will assign the lemma `to work` to words such as `worked`.
- **Gender tagging:** In gendered and supported languages, LinguaCafe will prepend nouns with additional information based on the words' gender. 

## Supported Languages

LinguaCafe supports the following languages:

| Flag                                                          | Language  | DeepL   | Lemma generation | Gender tagging | Dictionaries          |
|:-------------------------------------------------------------:|:---------:|:-------:|:----------------:|:-------------------:|-----------------------|
| <img src='../public/images/flags/chinese.png' width='25'>   | Chinese   | &check; |                  |                     | wiktionary, cc-cedict |
| <img src='../public/images/flags/czech.png' width='25'>     | Czech     | &check; |                  |                     | wiktionary, dict cc   |
| <img src='../public/images/flags/dutch.png' width='25'>     | Dutch     | &check; | &check;          |                     | dict cc               |
| <img src='../public/images/flags/finnish.png' width='25'>   | Finnish   | &check; | inaccurate       |                     | wiktionary, dict cc   |
| <img src='../public/images/flags/french.png' width='25'>    | French    | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='../public/images/flags/german.png' width='25'>    | German    | &check; | &check;          | &check;             | wiktionary, dict cc   |
| <img src='../public/images/flags/italian.png' width='25'>   | Italian   | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='../public/images/flags/japanese.png' width='25'>  | Japanese  | &check; | &check;          |                     | jmdict, wiktionary    |
| <img src='../public/images/flags/korean.png' width='25'>    | Korean    | &check; | &check;          |                     | wiktionary, kengdic   |
| <img src='../public/images/flags/norwegian.png' width='25'> | Norwegian | &check; | &check;          | &check;             | wiktionary, dict cc   |
| <img src='../public/images/flags/russian.png' width='25'>   | Russian   | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='../public/images/flags/spanish.png' width='25'>   | Spanish   | &check; | &check;          |                     | wiktionary, dict cc   |
| <img src='../public/images/flags/swedish.png' width='25'>   | Swedish   | &check; | &check;          |                     | dict cc               |
| <img src='../public/images/flags/ukrainian.png' width='25'> | Ukrainian | &check; |                  |                     | wiktionary            |
| <img src='../public/images/flags/welsh.png' width='25'>     | Welsh     |         |                  |                     | wiktionary, eurfa     |

> [!NOTE]  
> Chinese: Mandarin language with simplified Chinese characters.
>
> **TODO:** Elaborate more here.

# Library

LinguaCafe allows you to manage all imported texts on the **Library** page. The imported texts are arranged and labeled as **books** and **chapters**, but they can be anything like subtitles, podcast transcripts or news articles.  

Each book and chapter shows a count for unique words, known words, highlighted words, and new words, all of which allows you to estimate how difficult a text will be for your level.
## Books

LinguaCafe organizes your reading content into **books** with **chapters**, however it uses the term **book** in a broader sense because the imported text can come from different  sources:
- Plain text
- Text file
- E-book file
- Youtube subtitle
- Subtitle file
- Jellyfin's external subtitle
- Webpage

Whenever you import some text into LinguaCafe, regardless of the source, you've the choice to
* **Select its library location.** You can choose to add a new chapter to an already existing book, or create an entirely different book. 
* **Select how the text is processed and split.** You can use either the **Simple** or **Detailed** process, which determines whether the processed text will be enriched with additional information such as dictionary form, gender, or reading. You can also set the **Maximum number of characters per chapter**; by default, the maximum number of characters is set to 3000 and there's a maximum of 15000. 

## Imported text types

| Import source | Description |
|:--------------|:------------|
| Plain text | Paste text directly into a simple editor, which you can make into a chapter. |
| Text file | Upload a text file and then make into a chapter. |
| Ebook file | Upload an ebook file and make into a book. |
| Youtube subtitle | Import the subtitle from a Youtube video by simply pasting the video URL into the **Youtube url** field and pressing <kbd>Enter</kbd> in your keyboard. This will grab the video's subtitle, which you can then edit, and make into a chapter. |
| Subtitle file | Import text from a subtitle file, which you can edit, and make into a chapter. |
| Jellyfin's external subtitle | Import the text from a Jellyfin's external subtitle file, which you can make into a chapter. See [Configuring Jellyfin](usage#jellyfin) for more info on how to configure it. |
| Webpage | Import the text from a website using the page URL. You simply paste the URL into the **Website url** field and press <kbd>Enter</kbd> in your keyboard. This will grab the page's text, which you can then edit, and make into a chapter. |

# Reading

Supplementing your language learning with LinguaCafe boils down to reading and reviewing the words you're learning in context. The first time you start reading in LinguaCafe, all your words will be new, which means you haven't seen them before in LinguaCafe. As you read more, the number of words you don't known will naturally decrease.

Whenever you select a word you don't know, and you add a translation for it, be it from the built-in vocabulary search or manually, you will start to learn this word. As you review the words you're learning, you start getting closer to learning them. Once you learn a word, this word becomes known. If you already know a word, you can mark it as known manually so it doesn't show up in reviews.

There are words you don't want to learn or simply want to ignore, for example proper nouns, foreign words, etc, which is why LinguaCafe also allows you to ignore words, in which case they won't count towards the learned word statistics.

In LinguaCafe, we can summarize the words by level as follows:

- **New**, words that you haven't seen before, haven't started to learn yet, or haven't marked as known or ignored.
- **Learning**, words that you're currently learning. LinguaCafe uses integers in the range from 1 to 7 to determine how often these should appear in reviews. The closer a word is to 0, the closer you're to learning it and the less it appears in reviews.
- **Known**, words that have reached the level 0 via reviews or that you checked in the **Vocabulary Sidebar** by clicking the checkmark.
- **Ignored**, words that you've explicitly ignored by clicking the cross in the **Vocabulary Sidebar**. 

New words and words you're currently learning are highlighted, however the colors used depend on the theme you're using. Here's a summary for the default themes:

| Theme | New | Learning | Known | Ignored |
| :--- | :--- | ---- | ---- | ---- |
| Light | Yellow | Green | No highlighting | No highlighting |
| Dark | Yellow | Green | No highlighting | No highlighting |
| Eink | Underlined | Black | No highlighting | No highlighting |

In addition to saving words, LinguaCafe also allows you to save multiple words as a **phrase**. With your mouse, left-click on a word and drag it onto the next one until you've selected all the adjacent words you want. When you're done selecting words, you can add a translation for the phrase in the **Vocabulary Sidebar** or pop-up **Vocabulary Box**, set its level, and save it.

When you reach the end of a chapter, you can click the **Finished reading** button to set all words in the chapter to known. 

> [!NOTE]
> The effect of clicking the **Finished reading** button cannot be undone, thus exercise some caution when clicking it. If you don't know all the words in the chapter and/or you don't want to set them all to known, then don't click it. Words that have been set to anything, such as known, cannot be reverted back to new.

# Review

After reading your texts and creating highlighted words and phrases, you can review them on the **Review**. You can review words from a specific book, chapter, or you can review them all at once.  

LinguaCafe uses a spaced repetition system (SRS) similar to the Leitner system, but you can export your highlighted cards to Anki or other SRS software if you want. To update LinguaCafe's SRS settings, head over to **Admin** > **Reviews**, and follow the instructions.

# Goal Tracking

LinguaCafe tracks how many words you read, highlight and review daily. You can view and edit your daily goals on the **Home** page, and you can see your progress over time on the **Calendar**.  
  
You can also view your all time statistics on the bottom of **Home page**.

# Vocabulary

In LinguaCafe, you can search, edit and export your words on the **Vocabulary** page.

# Dictionaries

LinguaCafe comes with no dictionary files by default, but you can download and import them from different sources. Check [Importing dictionaries](usage#importing-dictionaries) for how to import dictionaries into LinguaCafe.

You can also use DeepL translator, which allows you to translate 500.000 characters/month for free with machine translation. To access the Deepl API, you'll need to create an [API key](https://support.deepl.com/hc/en-us/articles/360020695820-API-Key-for-DeepL-s-API) and add it in **Admin** > **API** > **DeepL**.

# Kanji and radicals

You can view information about the kanji you know.

# Anki

Currently Anki is supported if your server and Anki run on the same PC and have the [AnkiConnect](https://ankiweb.net/shared/info/2055492159) plugin installed.

To set up an Anki's connection, head over to **Admin** > **API** >**Anki**.

> [!NOTE]
>
>Future versions of LinguaCafe won't have this requirement.

# Themes and customization

LinguaCafe comes with a light, dark and e-ink theme.  
  
Mobile view is also supported. You can use LinguaCafe from any device that has a browser and at least 340px wide screen.

