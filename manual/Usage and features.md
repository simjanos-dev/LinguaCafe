# Usage and features
This section contains information related to how to use LinguaCafe after you have a working server.

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

> [!CAUTION]
> If you increase a chapter's length too much, the text reader will become slower.

> [!CAUTION]
> Do not import more text than you intend to actually use. Whenever you save a phrase, it will have to be indexed in every imported text, and if there are too many imported texts, adding a new phrase can become slower.

## Imported text types

| Import source | Description |
|:--------------|:------------|
| Plain text | Paste text directly into a simple editor, which you can make into a chapter. |
| Text file | Upload a text file and then make into a chapter. |
| Ebook file | Upload an ebook file and make into a book. |
| Youtube subtitle | Import the subtitle from a Youtube video by simply pasting the video URL into the **Youtube url** field and pressing <kbd>Enter</kbd> in your keyboard. This will grab the video's subtitle, which you can then edit, and make into a chapter. |
| Subtitle file | Import text from a subtitle file, which you make into a chapter. |
| Jellyfin's external subtitle | Import the text from a Jellyfin's external subtitle file, which you can make into a chapter. See the **Configuring Jellyfin** section for more info on how to configure it. |
| Webpage | Import the text from a website using the page URL. You simply paste the URL into the **Website url** field and press <kbd>Enter</kbd> in your keyboard. This will grab the page's text, which you can then edit, and make into a chapter. This import method is not supported for every language.|

# Reading

Supplementing your language learning with LinguaCafe boils down to reading and reviewing the words you're learning in context. The first time you start reading in LinguaCafe, all your words will be new, which means you haven't seen them before in LinguaCafe. As you read more, the number of words you don't known will naturally decrease.

Whenever you select a word you don't know, and you add a translation for it, be it from the built-in vocabulary search or manually, you will start to learn this word. As you review the words you're learning, you start getting closer to learning them. Once you learn a word, this word becomes known. If you already know a word, you can mark it as known manually so it doesn't show up in reviews.

There are words you don't want to learn or simply want to ignore, for example proper nouns, foreign words, etc, which is why LinguaCafe also allows you to ignore words, in which case they won't count towards the learned word statistics.

You can also use **hotkeys** to change a word's or a phrase's level without clicking them and for many other things. Click on the hotkeys button on the toolbar to see a list of all the available hotkeys.

In LinguaCafe, we can summarize the words by level as follows:

- **New**, words that you haven't seen before, or haven't interacted with it yet.
- **Learning**, words that you're currently learning. LinguaCafe uses numbers from 1 to 7 to determine how often these should appear in reviews. The closer a word is to 0, the closer you're to learning it and the less it appears in reviews.
- **Known**, words that have reached the level 0 via reviews or that you checked in the **Vocabulary** by clicking the checkmark.
- **Ignored**, words that you've explicitly ignored by clicking the cross in the **Vocabulary**. 

New words and words you're currently learning are highlighted, however the colors used depend on the theme you're using. Here's a summary for the default themes:

| Theme | New | Learning | Known | Ignored |
| :--- | :--- | ---- | ---- | ---- |
| Light | Yellow | Green | No highlighting | No highlighting |
| Dark | Yellow | Green | No highlighting | No highlighting |
| Eink | Underlined | Black | No highlighting | No highlighting |

In the default light and dark themes, the closer you are to learn a word, the less vivid the green highlighting will be. You can turn off this feature by modifying your theme on the **User settings** page.

In addition to saving words, LinguaCafe also allows you to save multiple words as a **phrase**. With your mouse, left-click on a word and drag it onto the next one until you've selected all the adjacent words you want. When you're done selecting words, you can add a translation for the phrase in the **Vocabulary Sidebar** or pop-up **Vocabulary Box**, set its level, and save it. 

When you reach the end of a chapter, you can click the **Finished reading** button to set all yellow words in the chapter to known and update the daily read words statistics.

> [!NOTE]
> The effect of clicking the **Finished reading** button cannot be undone easily, thus exercise some caution when clicking it. You can turn off auto setting the words to known by the **Finished reading** button in the text reader settings. You can also  set individual words' levels back to new again with a hotkey.


# Popup vocabularies

LinguaCafe currently has 4 types of vocabularies:
- Sidebar vocabulary
- Popup vocabulary
- Bottom sheet vocabulary
- Hover vocabulary

**Sidebar vocabulary** is the one you see by default on the right side on large enough screens.

**Popup vocabulary** is the one you see by default on smaller screens, or if you disable the sidebar vocabulary.

**Bottom sheet vocabulary** is the one you see by default on mobile screens.

All four of these work the same: they search for partial matches in the imported dictionaries when you click on a word or select a phrase, and they use lemma as a search term if available. They also show a DeepL translation if enabled.

**Hover vocabulary** works a bit differently, it is visible if you hover over a word or a phrase. It was designed to be minimalistic and compact. It only searches for exact matches, and does not display information about which word belongs to which dictionary. 

It shows 3 different type of results with different icons in this order:
- Manually saved definitions
- Dictionary searches
- DeepL translation

If you save multiple definition separated by `;` character, they will be displayed like a list in the **Hover vocabulary**.

> [!CAUTION]
> Japanese and Chinese readings displayed in the **Hover vocabulary** are machine generated if the you have not replaced them, thus cannot be trusted. For Japanese, please use the readings found in the JMDict dictionary. For Chinese, there are no accurate readings available currently.

> [!CAUTION]
> Since hover vocabulary searches for lemma if available, translations may also be incorrect here if the generated lemma was also incorrect. In my experience this is extremely rare, but if you need to be 100% sure for a word, you might want to use the **Sidebar**, **Popup** or the **Bottom sheet** vocabulary, where you can see excatly which word the definition belongs to from which dictionary.

# Text to speech

LinguaCafe supports text to speech with the [SpeechSynthesis](https://developer.mozilla.org/en-US/docs/Web/API/SpeechSynthesis) API. This feature is browser dependent. Most browsers support it, however I was only able to make it work on chrome browser on my desktop PC. 

You can use TTS on on the review and text reader pages, and you can select a TTS voice in the settings for each language, if your browser supports TTS.

This feature will probably be extended in the future with better AI TTS tools.

# Settings

**Text reader and Review page settings**: These settings are saved locally in your browser, because most of them are related to displaying things, and this allows different settings for different devices. 

**User settings page**: These settings are stored on the server, and they are the same on every device.

# Review

After reading your texts and creating highlighted words and phrases, you can review them on the **Review**. You can review words from a specific book, chapter, or you can review them all at once.

LinguaCafe uses a spaced repetition system (SRS) similar to the Leitner system, but you can export your highlighted cards to Anki or other SRS software if you want. To update LinguaCafe's SRS settings, head over to **Admin** > **Reviews**, and follow the instructions.

You can also ignore the spaced repetition system and review words without changing their data in practice review mode.

# Goal Tracking

LinguaCafe tracks how many words you read, highlight and review daily. You can view and edit your daily goals on the **Home** page, and you can see your progress over time on the **Calendar**.  
  
You can also view your all time statistics on the bottom of **Home page**.

# Vocabulary

You can search, edit and export your words on the **Vocabulary** page.

# Japanese and Chinese readings

> [!CAUTION]
> Japanese and Chinese readings are machine generated if the you have not replaced them, thus cannot be trusted. For Japanese, please use the readings found in the JMDict dictionary. For Chinese, there are no accurate readings available currently.

# Kanji and radicals

You can view information about the kanji you know on the **Kanji** page.

# Themes and customization

LinguaCafe comes with a light, dark and e-ink theme, and supports tablet and mobile views as well. You can use LinguaCafe from any device that has a browser and at least 340px wide screen.

You can also customize every color in LinguaCafe on the *User settings* page.

# Font types

You can upload font type files to LinguaCafe, and use those fonts on the text reader and review pages. You can upload files on the **Admin** > **Fonts** page, and select which languages do you want to enable for each font type.

# Mobile and tablet support

LinguaCafe supports the [Progressive Web App](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps) feature. This feature allows you to add LinguaCafe to your home screen, and use it as a fullscreen mobile application.