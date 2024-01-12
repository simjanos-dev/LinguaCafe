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

@Language.component("custom_sentence_splitter")
def custom_sentence_splitter(doc):    
    punctuations = ['NEWLINE', '？', '！', '。', '?', '!', '.', '»', '«']
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

norwegian_nlp = spacy.load("nb_core_news_sm", disable = ['ner', 'parser'])
norwegian_nlp.add_pipe("custom_sentence_splitter", first=True)

german_nlp = spacy.load("de_core_news_sm", disable = ['ner', 'parser'])
german_nlp.add_pipe("custom_sentence_splitter", first=True)

korean_nlp = spacy.load("ko_core_news_sm", disable = ['ner', 'parser'])
korean_nlp.add_pipe("custom_sentence_splitter", first=True)

spanish_nlp = spacy.load("es_core_news_sm", disable = ['ner', 'parser'])
spanish_nlp.add_pipe("custom_sentence_splitter", first=True)

chinese_nlp = spacy.load("zh_core_web_sm", disable = ['ner', 'parser'])
chinese_nlp.add_pipe("custom_sentence_splitter", first=True)

dutch_nlp = spacy.load("nl_core_news_sm", disable = ['ner', 'parser'])
dutch_nlp.add_pipe("custom_sentence_splitter", first=True)

finnish_nlp = spacy.load("fi_core_news_sm", disable = ['ner', 'parser'])
finnish_nlp.add_pipe("custom_sentence_splitter", first=True)

french_nlp = spacy.load("fr_core_news_sm", disable = ['ner', 'parser'])
french_nlp.add_pipe("custom_sentence_splitter", first=True)

italian_nlp = spacy.load("it_core_news_sm", disable = ['ner', 'parser'])
italian_nlp.add_pipe("custom_sentence_splitter", first=True)

swedish_nlp = spacy.load("sv_core_news_sm", disable = ['ner', 'parser'])
swedish_nlp.add_pipe("custom_sentence_splitter", first=True)

ukrainian_nlp = spacy.load("uk_core_news_sm", disable = ['ner', 'parser'])
ukrainian_nlp.add_pipe("custom_sentence_splitter", first=True)

russian_nlp = spacy.load("ru_core_news_sm", disable = ['ner', 'parser'])
russian_nlp.add_pipe("custom_sentence_splitter", first=True)

# used for splitting and parsing text
sentenceEndings = ['NEWLINE', '？', '！', '。', '?', '!', '.', '»', '«']
duplicateRemovalRegex = '(TMP_ST){2,}'

alphabetRegex = {
    'norwegian': r'([^a-zA-ZéóæøåÉÆØÅÓ])',
    'german': r'([^a-zA-ZäÄöÖüÜßẞ])',
    'spanish': r'([^a-zA-ZñÑ])'
}


# responds to http requests from the main PHP site
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

# returns a raw text and a tokenized text 
# of n .epub file cut into chunks
def importBook(request):
    postData = json.loads(request.body)
    
    # load book
    content = loadBook(postData['importFile'])
    content = content.replace('\r\n', ' NEWLINE ')
    content = content.replace('\n', ' NEWLINE ')

    # split text into sentences
    for sentenceEnding in sentenceEndings:
        content = content.replace(sentenceEnding, sentenceEnding + 'TMP_ST')
    sentences = content.split('TMP_ST')

    # split book into chunks
    chunks = list()
    processedChunks = list()
    for sentenceIndex, sentence in enumerate(sentences):
        if (len(processedChunks) == 0 or len(processedChunks[-1]) > postData['chunkSize']):
            chunks.append('')
            processedChunks.append('')

        chunks[-1] += sentences[sentenceIndex]
        chunks[-1] = chunks[-1].replace(' NEWLINE ', '\r\n')
        chunks[-1] = chunks[-1].replace('\xa0', ' ')
        processedChunks[-1] += sentences[sentenceIndex]


    #tokenize each chunk
    for chunkIndex, chunk in enumerate(processedChunks):
        if postData['textProcessingMethod'] == 'simple':
            processedChunks[chunkIndex] = tokenizeTextSimple(processedChunks[chunkIndex], postData['language'])
        else:
            processedChunks[chunkIndex] = tokenizeText(processedChunks[chunkIndex], postData['language'])

    return HttpResponse(json.dumps({'textChunks': chunks, 'processedChunks': processedChunks}), content_type="application/json")

# Cuts a text into sentences and words. Works like 
# tokenizer, but provides no additional data for words.
def tokenizeTextSimple(words, language):
    tokenizedWords = list()

    # split by sentences
    # TMP_ST is used as a temp string
    for sentenceEnding in sentenceEndings:
        words = words.replace(sentenceEnding, sentenceEnding + 'TMP_ST')

    sentences = words.split('TMP_ST')
    
    wordIndex = 0
    for sentenceIndex, sentence in enumerate(sentences):
        # split sentences into words
        sentences[sentenceIndex] = re.sub(alphabetRegex[language], r'TMP_ST\1TMP_ST', sentences[sentenceIndex])
        sentences[sentenceIndex] = re.sub(duplicateRemovalRegex, 'TMP_ST', sentences[sentenceIndex])
        sentences[sentenceIndex] = sentences[sentenceIndex].split('TMP_ST')

        # add empty token info
        for word in sentences[sentenceIndex]:
            if word == ' ' or word == '' or word == ' ':
                continue
            
            tokenizedWords.append({'w': word, 'r': '', 'l': '', 'lr': '', 'pos': '','si': sentenceIndex, 'g': ''})
            wordIndex = wordIndex + 1
    
    return tokenizedWords

# Tokenizes a text with spacy.
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

    if language == 'chinese':
        doc = chinese_nlp(words)
    
    if language == 'dutch':
        doc = dutch_nlp(words)
    
    if language == 'finnish':
        doc = finnish_nlp(words)
    
    if language == 'french':
        doc = french_nlp(words)
    
    if language == 'italian':
        doc = italian_nlp(words)

    if language == 'russian':
        doc = russian_nlp(words)

    if language == 'swedish':
        doc = swedish_nlp(words)

    if language == 'ukrainian':
        doc = ukrainian_nlp(words)

    for sentenceIndex, sentence in enumerate(doc.sents):
        for token in sentence:
            word = str(token.text)
            if word == ' ' or word == '' or word == ' ':
                continue
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

            tokenizedWords.append({'w': word, 'r': ''.join(reading), 'l': token.lemma_, 'lr': ''.join(lemmaReading), 'pos': token.pos_,'si': sentenceIndex, 'g': gender})
    return tokenizedWords

# loads n .epub file
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