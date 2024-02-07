from bottle import route, request, response, run, BaseRequest
BaseRequest.MEMFILE_MAX = 1024 * 1024 * 100
from spacy.language import Language
import sys
import json
import pykakasi
import spacy
import time
import re
import ebooklib 
import html
import pinyin
from ebooklib import epub
from youtube_transcript_api import YouTubeTranscriptApi
from youtube_transcript_api._errors import TranscriptsDisabled
from urllib import parse
from pysubparser import parser
from pysubparser.cleaners import formatting

# create emtpy sapce models
multi_nlp = None
japanese_nlp = None
hiraganaConverter = None
norwegian_nlp = None
german_nlp = None
korean_nlp = None
spanish_nlp = None
chinese_nlp = None
dutch_nlp = None
finnish_nlp = None
french_nlp = None
italian_nlp = None
swedish_nlp = None
ukrainian_nlp = None
russian_nlp = None

@Language.component("custom_sentence_splitter")
def custom_sentence_splitter(doc):    
    punctuations = ['NEWLINE', '？', '！', '。', '?', '!', '.', '»', '«']
    for token in doc[:-1]:
        if token.text in punctuations:
            doc[token.i+1].is_sent_start = True
        else:
            doc[token.i+1].is_sent_start = False
    return doc


def getTokenizerDoc(language, words):
    # load the tokenizer model for the first time
    if language == 'german':
        global german_nlp
        if german_nlp == None:
            german_nlp = spacy.load("de_core_news_sm", disable = ['ner'])
            german_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = german_nlp(words)
        
    if language == 'japanese':
        global japanese_nlp
        global hiraganaConverter
        if japanese_nlp == None:
            japanese_nlp = spacy.load("ja_core_news_sm", disable = ['ner', 'parser'])
            japanese_nlp.add_pipe("custom_sentence_splitter", first=True)
            hiraganaConverter = pykakasi.kakasi()
        doc = japanese_nlp(words)

    if language == 'korean':
        global korean_nlp
        if korean_nlp == None:
            korean_nlp = spacy.load("ko_core_news_sm", disable = ['ner', 'parser'])
            korean_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = korean_nlp(words)

    if language == 'norwegian':
        global norwegian_nlp
        if norwegian_nlp == None:
            norwegian_nlp = spacy.load("nb_core_news_sm", disable = ['ner', 'parser'])
            norwegian_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = norwegian_nlp(words)

    if language == 'spanish':
        global spanish_nlp
        if spanish_nlp == None:
            spanish_nlp = spacy.load("es_core_news_sm", disable = ['ner', 'parser'])
            spanish_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = spanish_nlp(words)

    if language == 'chinese':
        global chinese_nlp
        if chinese_nlp == None:
            chinese_nlp = spacy.load("zh_core_web_sm", disable = ['ner', 'parser'])
            chinese_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = chinese_nlp(words)
    
    if language == 'dutch':
        global dutch_nlp
        if dutch_nlp == None:
            dutch_nlp = spacy.load("nl_core_news_sm", disable = ['ner', 'parser'])
            dutch_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = dutch_nlp(words)
    
    if language == 'finnish':
        global finnish_nlp
        if finnish_nlp == None:
            finnish_nlp = spacy.load("fi_core_news_sm", disable = ['ner', 'parser'])
            finnish_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = finnish_nlp(words)
    
    if language == 'french':
        global french_nlp
        if french_nlp == None:
            french_nlp = spacy.load("fr_core_news_sm", disable = ['ner', 'parser'])
            french_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = french_nlp(words)
    
    if language == 'italian':
        global italian_nlp
        if italian_nlp == None:
            italian_nlp = spacy.load("it_core_news_sm", disable = ['ner', 'parser'])
            italian_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = italian_nlp(words)

    if language == 'russian':
        global russian_nlp
        if russian_nlp == None:
            russian_nlp = spacy.load("ru_core_news_sm", disable = ['ner', 'parser'])
            russian_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = russian_nlp(words)

    if language == 'swedish':
        global swedish_nlp
        if swedish_nlp == None:
            swedish_nlp = spacy.load("sv_core_news_sm", disable = ['ner', 'parser'])
            swedish_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = swedish_nlp(words)

    if language == 'ukrainian':
        global ukrainian_nlp
        if ukrainian_nlp == None:
            ukrainian_nlp = spacy.load("uk_core_news_sm", disable = ['ner', 'parser'])
            ukrainian_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = ukrainian_nlp(words)

    if language in ('welsh', 'czech'):
        global multi_nlp
        if multi_nlp == None:
            multi_nlp = spacy.load("xx_ent_wiki_sm", disable = ['ner'])
            multi_nlp.add_pipe("custom_sentence_splitter", first=True)
        doc = multi_nlp(words)
    return doc

# used for splitting and parsing text
sentenceEndings = ['NEWLINE', '？', '！', '。', '?', '!', '.', '»', '«']
duplicateRemovalRegex = '(TMP_ST){2,}'

alphabetRegex = {
    'norwegian': r'([^a-zA-ZéóæøåÉÆØÅÓ])',
    'german': r'([^a-zA-ZäÄöÖüÜßẞ])',
    'spanish': r'([^a-zA-ZñÑ])'
}

# used for german separable verbs
def get_separable_lemma(token):
    prefix = [c.text for c in token.children if c.dep_ == 'svp']
    if len(prefix) > 0:
        return prefix[0] + token.lemma_
    return token.lemma_

# Cuts a text into sentences and words. Works like 
# tokenizer, but provides no additional data for words.
def tokenizeTextSimple(words, language, sentenceIndexStart = 0):
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
            
            tokenizedWords.append({'w': word, 'r': '', 'l': '', 'lr': '', 'pos': '','si': sentenceIndex + sentenceIndexStart, 'g': ''})
            wordIndex = wordIndex + 1
    
    return tokenizedWords

# Tokenizes a text with spacy.
def tokenizeText(words, language, sentenceIndexStart = 0):
    global hiraganaConverter
    tokenizedWords = list()
    doc = getTokenizerDoc(language, words)
    

    for sentenceIndex, sentence in enumerate(doc.sents):
        for token in sentence:
            word = str(token.text)
            if word == ' ' or word == '' or word == ' ':
                continue
            
            #get lemma
            lemma = token.lemma_
            
            #get hiragana reading
            reading = list()
            lemmaReading = list()
            if language == 'japanese':
                result = hiraganaConverter.convert(token.text)
                for x in result:
                    reading.append(x['hira'])
                
                result = hiraganaConverter.convert(token.lemma_)
                for x in result:
                    lemmaReading.append(x['hira'])
            
                reading = ''.join(reading)
                lemmaReading = ''.join(lemmaReading)

            #get pinyin reading
            if language == 'chinese':
                reading = pinyin.get(word)
                lemmaReading = pinyin.get(lemma)

            #get gender
            gender = ''
            if language in ('norwegian', 'german'):
                gender = token.morph.get("Gender")

            if language == 'german' and token.pos_ == 'VERB':
                lemma = get_separable_lemma(token)
            
            tokenizedWords.append({'w': word, 'r': reading, 'l': lemma, 'lr': lemmaReading, 'pos': token.pos_,'si': sentenceIndex + sentenceIndexStart, 'g': gender})
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

# responds to http requests from the main PHP site
@route('/tokenizer', method='POST')
def tokenizer():
    response.headers['Content-Type'] = 'application/json'

    # start = time.time()
    rawText = request.json.get('raw_text')
    language = request.json.get('language')

    if type(rawText) == str:
        jsonWords = tokenizeText(rawText, language)
        return json.dumps(jsonWords)
    else:
        tokenizedText = list()
        for text in rawText:
            tokenizedText.append(tokenizeText(text, language))
        return json.dumps(tokenizedText)

# returns a raw text and a tokenized text 
# of n .epub file cut into chunks
@route('/tokenizer/import-book', method='POST')
def importBook():
    response.headers['Content-Type'] = 'application/json'
    chunkSize = request.json.get('chunkSize')
    textProcessingMethod = request.json.get('textProcessingMethod')
    importFile = request.json.get('importFile')
    language = request.json.get('language')
    
    # load book
    content = loadBook(importFile)
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
        if (len(processedChunks) == 0 or len(processedChunks[-1].replace(' NEWLINE ', '')) > chunkSize):
            chunks.append('')
            processedChunks.append('')

        chunks[-1] += sentences[sentenceIndex]
        chunks[-1] = chunks[-1].replace(' NEWLINE ', '\r\n')
        chunks[-1] = chunks[-1].replace('\xa0', ' ')
        processedChunks[-1] += sentences[sentenceIndex]


    # tokenize each chunk
    for chunkIndex, chunk in enumerate(processedChunks):
        if textProcessingMethod == 'simple':
            processedChunks[chunkIndex] = tokenizeTextSimple(processedChunks[chunkIndex], language)
        else:
            processedChunks[chunkIndex] = tokenizeText(processedChunks[chunkIndex], language)

    return json.dumps({'textChunks': chunks, 'processedChunks': processedChunks})

# cuts the text given in post data into chunks, and tokenizes them
@route('/tokenizer/import-text', method='POST')
def importText():
    response.headers['Content-Type'] = 'application/json'
    chunkSize = request.json.get('chunkSize')
    textProcessingMethod = request.json.get('textProcessingMethod')
    importText = request.json.get('importText')
    language = request.json.get('language')
    
    # load text
    text = importText.replace('\r\n', ' NEWLINE ')
    text = text.replace('\n', ' NEWLINE ')

    # split text into sentences
    for sentenceEnding in sentenceEndings:
        text = text.replace(sentenceEnding, sentenceEnding + 'TMP_ST')
    sentences = text.split('TMP_ST')

    # split the text into chunks
    chunks = list()
    processedChunks = list()
    for sentenceIndex, sentence in enumerate(sentences):
        if (len(processedChunks) == 0 or len(processedChunks[-1].replace(' NEWLINE ', '')) > chunkSize):
            chunks.append('')
            processedChunks.append('')

        chunks[-1] += sentences[sentenceIndex]
        chunks[-1] = chunks[-1].replace(' NEWLINE ', '\r\n')
        chunks[-1] = chunks[-1].replace('\xa0', ' ')
        processedChunks[-1] += sentences[sentenceIndex]

    #tokenize each chunk
    for chunkIndex, chunk in enumerate(processedChunks):
        if textProcessingMethod == 'simple':
            processedChunks[chunkIndex] = tokenizeTextSimple(processedChunks[chunkIndex], language)
        else:
            processedChunks[chunkIndex] = tokenizeText(processedChunks[chunkIndex], language)

    return json.dumps({'textChunks': chunks, 'processedChunks': processedChunks})

# cuts the text given in post data into chunks, and tokenizes them
@route('/tokenizer/import-subtitles', method='POST')
def importSubtitles():
    response.headers['Content-Type'] = 'application/json'
    chunkSize = request.json.get('chunkSize')
    textProcessingMethod = request.json.get('textProcessingMethod')
    importSubtitles = json.loads(request.json.get('importSubtitles'))
    language = request.json.get('language')

    # split the text into chunks
    chunks = list()
    processedChunks = list()
    chunkTimeStamps = list()
    currentChunkSize = 0
    currentChunkSentenceIndex = 0
    for subtitleIndex, subtitle in enumerate(importSubtitles):
        if (len(processedChunks) == 0 or currentChunkSize > chunkSize):
            currentChunkSize = 0
            currentChunkSentenceIndex = 0
            chunks.append('')
            processedChunks.append(list())
            chunkTimeStamps.append(list())

    
        text = importSubtitles[subtitleIndex]['text'].replace('\r\n', ' NEWLINE ')
        text = text.replace('\n', ' NEWLINE ')

        # add subtitle to raw chunk
        chunks[-1] += text
        chunks[-1] = chunks[-1].replace(' NEWLINE ', '\r\n')
        chunks[-1] = chunks[-1].replace('\xa0', ' ')

        # tokenize text
        if textProcessingMethod == 'simple':
            tokenizedText = tokenizeTextSimple(text, language, currentChunkSentenceIndex)
        else:
            tokenizedText =  tokenizeText(text, language, currentChunkSentenceIndex)

        # set new sentence index
        currentChunkSentenceIndex = tokenizedText[-1]['si'] + 1

        # add timestamp to chunk array
        chunkTimeStamps[-1].append({
            'start': importSubtitles[subtitleIndex]['start'],
            'end': importSubtitles[subtitleIndex]['end'],
            'sentenceIndexStart': tokenizedText[0]['si'],
            'sentenceIndexEnd': tokenizedText[-1]['si']
        })
                    
        ## add tokenized text to processed chunk
        processedChunks[-1] = processedChunks[-1] + tokenizedText

        # increase current chunk size
        currentChunkSize += len(text.replace(' NEWLINE ', ''))
        print(currentChunkSize, len(text.replace(' NEWLINE ', '')), text.replace(' NEWLINE ', ''), file=sys.stderr)

    return json.dumps({'textChunks': chunks, 'processedChunks': processedChunks, 'timestamps': chunkTimeStamps})

@route('/tokenizer/get-youtube-subtitle-list', method='POST')
def getYoutubeSubtitles():
    response.headers['Content-Type'] = 'application/json'
    url = request.json.get('url')
    
    parsedUrl = parse.urlparse(url)
    videoId = parse.parse_qs(parsedUrl.query)['v'][0]

    try:
        subtitles = YouTubeTranscriptApi.list_transcripts(videoId)
    except TranscriptsDisabled: 
        return json.dumps(list())

    subtitleList = list()
    for subtitle in subtitles:
        subtitleList.append({
            'language': subtitle.language, 
            'languageLowerCase': subtitle.language.lower(), 
            'languageCode': subtitle.language_code, 
            'text': '\n'.join(line['text'] for line in subtitle.fetch())
        })

    
    return json.dumps(subtitleList)

@route('/tokenizer/get-subtitle-file-content', method='POST')
def getYoutubeSubtitles():
    response.headers['Content-Type'] = 'application/json'
    fileName = request.json.get('fileName')
    subtitleContent = list()
    
    subtitles = parser.parse(fileName)
    subtitles = formatting.clean(subtitles)
    for subtitle in subtitles:
        start = str(subtitle.start).split('.')[0]
        end = str(subtitle.end).split('.')[0]
        subtitleContent.append({
            'text': str(subtitle.text),
            'start': start,
            'end': end
        })

    # try:
    # except TranscriptsDisabled: 
    #     return json.dumps(list())


    return json.dumps(subtitleContent)

run(host='0.0.0.0', port=8678, reloader=True, debug=True)