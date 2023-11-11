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



# create tokenizers
japanese_nlp = spacy.load("ja_core_news_sm")
japanese_nlp.add_pipe("custom_sentence_splitter", first=True)
hiraganaConverter = pykakasi.kakasi()

norwegian_nlp = spacy.load("nb_core_news_md")
norwegian_nlp.add_pipe("custom_sentence_splitter", first=True)

german_nlp = spacy.load("de_core_news_md")
german_nlp.add_pipe("custom_sentence_splitter", first=True)

korean_nlp = spacy.load("ko_core_news_md")
korean_nlp.add_pipe("custom_sentence_splitter", first=True)

spanish_nlp = spacy.load("es_core_news_md")
spanish_nlp.add_pipe("custom_sentence_splitter", first=True)

def tokenizeText(words, language):
    tokenizedWords = list()
    if language == 'german':
        doc = german_nlp(words, disable = ['ner'])
        
    if language == 'japanese':
        doc = japanese_nlp(words, disable = ['ner'])
    

    if language == 'korean':
        doc = korean_nlp(words, disable = ['ner'])

    if language == 'norwegian':
        doc = norwegian_nlp(words, disable = ['ner'])

    if language == 'spanish':
        doc = spanish_nlp(words, disable = ['ner'])


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