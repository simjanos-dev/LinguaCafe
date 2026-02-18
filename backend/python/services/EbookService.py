import lxml_html_clean
import lxml.html
from lxml.html import html5parser
from ebooklib import epub
import ebooklib
import re
import base64
from enum import Enum, auto

from . import TokenizerConfig

config = TokenizerConfig.TokenizerConfig()


class TextProcessingMethod(Enum):
    plaintext = auto()
    preserve_block_tags = auto()

class EbookService:
    def loadEbookIntoChunks(
        self, importFile, language, chunkSize, textProcessingMethod: TextProcessingMethod, chapterSortMethod
    ):
        content = self.loadEbookFile(importFile, chapterSortMethod, textProcessingMethod)
        content = content.replace('\r\n', ' NEWLINE ')
        content = content.replace('\n', ' NEWLINE ')

        # split text into sentences
        for sentenceEnding in config.sentenceEndings:
            content = content.replace(sentenceEnding, sentenceEnding + 'TMP_ST')
        sentences = content.split('TMP_ST')

        # split text into chunks
        chunks = list()
        for sentenceIndex, sentence in enumerate(sentences):
            if len(chunks) == 0 or len(chunks[-1].replace(' NEWLINE ', '')) > chunkSize:
                chunks.append('')

            chunks[-1] += sentences[sentenceIndex]
            chunks[-1] = chunks[-1].replace(' NEWLINE ', '\r\n')
            chunks[-1] = chunks[-1].replace('\xa0', ' ')

        # Extract cover image
        coverImage = self.extractCoverImage(importFile)

        return {
            'chunks': chunks,
            'coverImage': coverImage
        }

    def processPage(self, page: str, textProcessingMethod: TextProcessingMethod) -> str:
        content = ''
        match textProcessingMethod:
            case TextProcessingMethod.plaintext:
                # needed to removed extra div created by cleaner...
                page = lxml.html.fromstring(page).text_content()
                content += page
            case TextProcessingMethod.preserve_block_tags:
                content = ''
                block_tags = [
                    'p',
                    r'{http://www.w3.org/1999/xhtml}p',
                    'div',
                    r'{http://www.w3.org/1999/xhtml}div',
                    'img',
                    r'{http://www.w3.org/1999/xhtml}img',
                ]
                element = html5parser.fromstring(page)
                for sub_element in element.iter():
                    # add leading linebreak text from block tags for readability
                    if sub_element.tag in block_tags:
                        content += "\n\n"

                    # concat text before the first subelement
                    if leading_text := sub_element.text:
                        content += leading_text

                    # concat text after an element's end tag, but before the next sibling's start tag
                    if (tail_text := sub_element.tail):
                        # In the case that the tail_text contains whitespace between inline (non-block) tags
                        # the tail_text should not be included
                        if not tail_text.isspace():
                            content += tail_text

        return content

                


    def loadEbookFile(self, file, sortMethod, textProcessingMethod: TextProcessingMethod):
        # rp and rt tags are used in adding prononciation over words, we need to remove the content of the tags

        content = ''
        book = epub.read_epub(file)
        items = list(book.get_items())

        # ebooks have 2 ways to order chapters
        if sortMethod == 'spine':
            sortedItems = list()
            for item in enumerate(book.spine):
                sortedItems.append(book.get_item_with_id(item[1][0]))
        else:
            sortedItems = items

        for item in sortedItems:
            if item.get_type() == ebooklib.ITEM_DOCUMENT:
                # clean_html cannot be passed bytes but it cannot be passed a str
                # with explicit encoding either. So you must convert it to a string
                # and then use RegEx to remove the encoding declaration
                content_str = item.get_content().decode()
                content_str = re.sub(r'<\?xml[^>]+\?>', '', content_str, count=1)
                cleaner = lxml_html_clean.Cleaner(
                    allow_tags=[],
                    remove_unknown_tags=False,
                    kill_tags=['rp', 'rt'],
                    page_structure=False,
                )
                epubPage = cleaner.clean_html(content_str)
                content += self.processPage(epubPage, textProcessingMethod)

        return content

    def extractCoverImage(self, file):
        """
        Extract cover image from epub file and return as base64 string
        Uses ebooklib's built-in cover handling
        """
        try:
            book = epub.read_epub(file)
            
            # Try to get cover image using ebooklib's metadata
            cover_image_data = None
            
            # Attempt to find cover image using metadata
            cover_metadata = book.get_metadata('OPF', 'cover')
            if cover_metadata:
                cover_id = cover_metadata[0][1].get('content')
                if cover_id:
                    cover_item = book.get_item_with_id(cover_id)
                    if cover_item:
                        cover_image_data = cover_item.get_content()
            
            # Attempt to find cover image by looking for items of type ITEM_COVER
            if not cover_image_data:
                for item in book.get_items_of_type(ebooklib.ITEM_COVER):
                    cover_image_data = item.get_content()
                    break
            
            # Attempt to find cover images by type ITEM_IMAGE with 'cover' in the name
            if not cover_image_data:
                for item in book.get_items_of_type(ebooklib.ITEM_IMAGE):
                    item_name = item.get_name().lower()
                    if 'cover' in item_name:
                        cover_image_data = item.get_content()
                        break
            
            # Encode to base64 if found
            if cover_image_data:
                return base64.b64encode(cover_image_data).decode('utf-8')
            
            return None
            
        except Exception as e:
            # If any error occurs log and return
            print(f"Error extracting cover image: {str(e)}")
            return None
