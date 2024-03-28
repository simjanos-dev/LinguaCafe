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
### Plain text

LinguaCafe allows you to paste text directly into a simple editor, which you can make into a chapter.

![Importing plain text into LinguaCafe](/GithubImages/LibraryCover.jpg)

### Text file

LinguaCafe allows you to upload a text file, edit its content, and then make into a chapter.

![Importing text from a text file into LinguaCafe](/GithubImages/LibraryCover.jpg)

### Ebook file

LinguaCafe allows you to upload an ebook file and make into a book. Currently only EPUB files are accepted.


![Importing text an ebook file into LinguaCafe](/GithubImages/LibraryCover.jpg)
### Youtube subtitle

LinguaCafe allows you to import the subtitle from a Youtube video by simply pasting the video URL into the **Youtube url** field and pressing <kbd>Enter</kbd> in your keyboard. This will grab the video's subtitle, which you can then edit, and make into a chapter.

![Importing text from a Youtube video into LinguaCafe](/GithubImages/LibraryCover.jpg)

### Subtitle file

LinguaCafe allows you to import text from subtitle file, which you can edit, and make into a chapter. 

![Importing text from a subtitle file into LinguaCafe](/GithubImages/LibraryCover.jpg)
### Jellyfin's external subtitle

LinguaCafe allows you to import the text from a Jellyfin's external subtitle file, which you can make into a chapter. See [Configuring Jellyfin](./configuring-jellyfin.md) for more info on how to configure it.

![Importing text from Jellyfin's external subtitle file into LinguaCafe](/GithubImages/LibraryCover.jpg)
### Webpage

LinguaCafe allows you to import the text from a website using the page URL. You simply paste the URL into the **Website url** field and press <kbd>Enter</kbd> in your keyboard. This will grab the page's text, which you can then edit, and make into a chapter.

![Importing text from a webpage into LinguaCafe](/GithubImages/LibraryCover.jpg)