#!/bin/python3.8
from konoha import WordTokenizer, SentenceTokenizer
import pykakasi
import mysql.connector
import json

import spacy

stokenizer = SentenceTokenizer()
tokenizer = WordTokenizer('MeCab')
hiraganaConverter = pykakasi.kakasi()

mydb = mysql.connector.connect(
  host="localhost",
  user="webserver",
  password="webserver",
  database="langapp"
)

mycursor = mydb.cursor()

# tokenize latin alphabet texts
# mycursor.execute("SELECT id,raw_text FROM lessons WHERE language = 'norwegian' AND processed_text = ''")
# myresult = mycursor.fetchall()
# for row in myresult:
#   nlp = spacy.load("nb_core_news_sm")
#   nlp.disable_pipe("parser")
#   nlp.enable_pipe("senter")
#   doc = nlp(row[1])
#   jsonPassableSentences = list()
#   for sentence in doc.sents:
#     jsonPassableWords = list()
#     for word in sentence:
#       jsonPassableWords.append(str(word))
#     jsonPassableSentences.append(jsonPassableWords)
#   jsonText = json.dumps(jsonPassableSentences)
#   sql = "UPDATE lessons SET processed_text = %s WHERE id = %s"
#   values = (jsonText, row[0])
#   mycursor.execute(sql, values)
# mydb.commit()

# tokenize japanese texts
mycursor.execute("SELECT id, CONVERT(raw_text USING utf8) as raw_text FROM lessons WHERE language = 'japanese' AND processed_text = ''")
myresult = mycursor.fetchall()
for row in myresult:
  tokenizedSentences = stokenizer.tokenize(row[1])
  jsonWords = list()
  for sentenceIndex, sentence in enumerate(tokenizedSentences):
    words = tokenizer.tokenize(sentence)
    for word in words:
      jsonWords.append({'word': str(word), 'sentenceIndex': sentenceIndex})
  jsonText = json.dumps(jsonWords)
  sql = "UPDATE lessons SET processed_text = %s WHERE id = %s"
  values = (jsonText, row[0])
  mycursor.execute(sql, values)
mydb.commit()

# get word readings
mycursor.execute("SELECT id,word FROM encountered_words WHERE language = 'japanese' AND reading = ''")
myresult = mycursor.fetchall()

for row in myresult:
  result = hiraganaConverter.convert(row[1])
  sql = "UPDATE encountered_words SET reading = %s WHERE id = %s"
  hiraganaFull = list()
  for x in result:
    hiraganaFull.append(x['hira'])
  values = (''.join(hiraganaFull), row[0])
  mycursor.execute(sql, values)
mydb.commit()

# get base word readings
mycursor.execute("SELECT id,base_word FROM encountered_words WHERE language = 'japanese' AND base_word <> '' AND base_word_reading = ''")
myresult = mycursor.fetchall()

for row in myresult:
  result = hiraganaConverter.convert(row[1])
  sql = "UPDATE encountered_words SET base_word_reading = %s WHERE id = %s"
  hiraganaFull = list()
  for x in result:
    hiraganaFull.append(x['hira'])
  values = (''.join(hiraganaFull), row[0])
  mycursor.execute(sql, values)
mydb.commit()



# tokenize japanese flash cards
mycursor.execute("SELECT id,sentence_raw FROM flash_cards WHERE sentence_processed = '' AND language = 'japanese'")
myresult = mycursor.fetchall()

for row in myresult:
  words = tokenizer.tokenize(row[1])
  jsonPassableWords = list()
  reading = list()
  for word in words:
    jsonPassableWords.append(str(word))

    #get reading
    hiragana = hiraganaConverter.convert(str(word))
    hiraganaFull = list()
    for x in hiragana:
      hiraganaFull.append(x['hira'])
    reading.append(str(''.join(hiraganaFull)))
    
  #update sentence
  jsonText = json.dumps(jsonPassableWords)
  sql = "UPDATE flash_cards SET sentence_processed = %s WHERE id = %s"
  values = (jsonText, row[0])
  mycursor.execute(sql, values)

  #update reading
  sql = "UPDATE flash_cards SET reading = %s WHERE id = %s AND reading = ''"
  values = (' '.join(reading), row[0])
  mycursor.execute(sql, values)

mydb.commit()

# tokenize norwegian
# mycursor.execute("SELECT id,sentence_raw FROM flash_cards WHERE sentence_processed = '' AND language = 'norwegian'")
# myresult = mycursor.fetchall()

# for row in myresult:
#   nlp = spacy.load("nb_core_news_sm")
#   nlp.disable_pipe("senter")
#   nlp.enable_pipe("parser")
#   doc = nlp(row[1])
#   jsonPassableWords = list()
#   for token in doc:
#     jsonPassableWords.append(str(token.text))
#   jsonText = json.dumps(jsonPassableWords)
#   sql = "UPDATE sentences SET sentence_processed = %s WHERE id = %s"
#   values = (jsonText, row[0])
#   mycursor.execute(sql, values)
# mydb.commit()
