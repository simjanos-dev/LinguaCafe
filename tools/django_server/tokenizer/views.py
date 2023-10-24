from django.shortcuts import render
from django.http import HttpResponse
from spacy.language import Language
import json
import pykakasi
import spacy
import time

@Language.component("custom_sentence_splitter")
def custom_sentence_splitter(doc):
    punctuations = ['NEWLINE', '？', '！', '。', '?', '!', '.']
    for token in doc[:-1]:
        if token.text in punctuations:
            doc[token.i+1].is_sent_start = True
        else:
            doc[token.i+1].is_sent_start = False
    return doc


japanese_nlp = spacy.load("ja_core_news_sm")
japanese_nlp.add_pipe("custom_sentence_splitter", first=True)
# japanese_nlp.max_length = 1500000
hiraganaConverter = pykakasi.kakasi()

norwegian_nlp = spacy.load("nb_core_news_sm")
norwegian_nlp.add_pipe("custom_sentence_splitter", first=True)
# norwegian_nlp.max_length = 1500000

def tokenizeText(words, language):
    tokenizedWords = list()
    if language == 'japanese':
        doc = japanese_nlp(words, disable = ['ner'])
    
    if language == 'norwegian':
        doc = norwegian_nlp(words, disable = ['ner'])

    for sentenceIndex, sentence in enumerate(doc.sents):
        for token in sentence:
            reading = list()
            lemmaReading = list()
            #get reading
            result = hiraganaConverter.convert(token.text)
            for x in result:
                reading.append(x['hira'])
            
            #get lemma reading
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