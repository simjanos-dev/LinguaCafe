#!/bin/python3.8
from konoha import WordTokenizer, SentenceTokenizer
import pykakasi
import sys
import mysql.connector
import json

stokenizer = SentenceTokenizer()
tokenizer = WordTokenizer('MeCab')
hiraganaConverter = pykakasi.kakasi()
# get word readings

sentences = list()
for sentence in sys.argv[1:]:
  result = tokenizer.tokenize(sentence)
  result = hiraganaConverter.convert(sentence)
  hiraganaFull = list()
  for x in result:
    #hiraganaFull.append({'word': x['hira'], 'reading': })
    print(x)
  sentences.append(hiraganaFull)
print(json.dumps(sentences))