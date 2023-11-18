from django.shortcuts import render
from django.http import HttpResponse
from spacy.language import Language
import json
import pykakasi
import spacy
import time
import re
import ebooklib 
import html
from ebooklib import epub


sentenceEndings = ['NEWLINE', '？', '！', '。', '?', '!', '.', '»', '«']
wordEndings = [' ', '？', '！', '。', '?', '!', '.', ',', '"', '\'']

@Language.component("custom_sentence_splitter")
def custom_sentence_splitter(doc):    
    punctuations = ['NEWLINE', '？', '！', '。', '?', '!', '.']
    for token in doc[:-1]:
        if token.text in punctuations:
            doc[token.i+1].is_sent_start = True
        else:
            doc[token.i+1].is_sent_start = False
    return doc


# create tokenizers
japanese_nlp = spacy.load("ja_core_news_sm", disable = ['ner', 'parser'])
japanese_nlp.add_pipe("custom_sentence_splitter", first=True)
hiraganaConverter = pykakasi.kakasi()

norwegian_nlp = spacy.load("nb_core_news_md", disable = ['tok2vec', 'parser', 'attribute_ruler', 'ner'])
norwegian_nlp.add_pipe("custom_sentence_splitter", first=True)
norwegian_nlp.max_length = 2131689

german_nlp = spacy.load("de_core_news_md", disable = ['ner', 'parser'])
german_nlp.add_pipe("custom_sentence_splitter", first=True)

korean_nlp = spacy.load("ko_core_news_md", disable = ['ner', 'parser'])
korean_nlp.add_pipe("custom_sentence_splitter", first=True)

spanish_nlp = spacy.load("es_core_news_md", disable = ['ner', 'parser'])
spanish_nlp.add_pipe("custom_sentence_splitter", first=True)

def tokenizeText(words, language):
    tokenizedWords = list()
    if language == 'german':
        doc = german_nlp(words)
        
    if language == 'japanese':
        doc = japanese_nlp(words)

    if language == 'korean':
        doc = korean_nlp(words)

    if language == 'norwegian':
        doc = norwegian_nlp(words)

    if language == 'spanish':
        doc = spanish_nlp(words)

    for sentenceIndex, sentence in enumerate(doc.sents):
        for token in sentence:
            #get reading
            reading = list()
            lemmaReading = list()
            if language == 'japanese':
                result = hiraganaConverter.convert(token.text)
                for x in result:
                    reading.append(x['hira'])
                
                result = hiraganaConverter.convert(token.lemma_)
                for x in result:
                    lemmaReading.append(x['hira'])

            gender = ''
            if language == 'norwegian':
                gender = token.morph.get("Gender")

            tokenizedWords.append({'w': str(token.text), 'r': ''.join(reading), 'l': token.lemma_, 'lr': ''.join(lemmaReading), 'pos': token.pos_,'si': sentenceIndex, 'g': gender})
    return tokenizedWords

def tokenizer(request):
    start = time.time()

    postData = json.loads(request.body)
    language = postData['language']
    if type(postData['raw_text']) == str:
        jsonWords = tokenizeText(postData['raw_text'], language)
        return HttpResponse(json.dumps(jsonWords), content_type="application/json")
    else:
        tokenizedText = list()
        for text in postData['raw_text']:
            tokenizedText.append(tokenizeText(text, language))
        return HttpResponse(json.dumps(tokenizedText), content_type="application/json")

def loadBook(file):
    htmlPattern = re.compile('<.*?>') 
    
    book = epub.read_epub(file)
    content = ''

    for item in book.get_items():
        if item.get_type() == ebooklib.ITEM_DOCUMENT:
            # print('file: ', item.get_name())
            epubPage = item.get_content().decode('UTF-8')
            epubPage = html.unescape(epubPage)
            epubPage = re.sub(htmlPattern, '', epubPage)

            content = content + epubPage

    return str(content)

def importBook(request):
    # text used temporarily for splitting
    splitTempText = 'öÖÜü'
    
    content = loadBook('/app/Harry Potter and the Deathly Hallows [NO].epub')
    content = content.replace('\r\n', ' NEWLINE ')
    content = content.replace('\r', ' NEWLINE ')

    duplicateRemovalRegex = '(ENDWORD){2,}'
    norwegianRegex = '([^a-zA-Zæøå])'

    # split by sentences
    for sentenceEnding in sentenceEndings:
        content = content.replace(sentenceEnding, sentenceEnding + splitTempText)

    # split by words
    sentences = content.split(splitTempText)
    tokenizedWords = list()
    wordIndex = 0
    for sentenceIndex, sentence in enumerate(sentences):
        # split sentences into words
        sentences[sentenceIndex] = re.sub(norwegianRegex, r'öÖÜü\1öÖÜü', sentences[sentenceIndex])
        sentences[sentenceIndex] = re.sub(duplicateRemovalRegex, splitTempText, sentences[sentenceIndex])
        sentences[sentenceIndex] = sentences[sentenceIndex].split(splitTempText)

        # add empty token info
        for word in sentences[sentenceIndex]:
            if word == ' ':
                continue
                
            tokenizedWords.append({'w': word, 'r': '', 'l': '', 'lr': '', 'pos': '','si': sentenceIndex, 'g': ''})
            wordIndex = wordIndex + 1
    
    return HttpResponse(json.dumps(tokenizedWords), content_type="application/json")
