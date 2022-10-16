#!/usr/bin/env python3 
import os
import json

index = 0
with open('./jmdict_processed.txt', 'w') as outputFile:
    with open('./jmdict.txt') as file:
        for line in file:
            data = line.replace('\n', '').split('|')
            if len(data[1]) > 0:
                processedConjugations = list()
                jmdictConjugations = os.popen('python3 conj.py ' + data[1] + ' ' + data[0]).read()
                for conjugation in jmdictConjugations.splitlines():
                    conjugationData = conjugation.split()
                    if conjugationData[0] == 'Notes:':
                        break
                    
                    if conjugationData[-1][-1] == ']':
                        conjugationData[-1] = conjugationData[-1][:-3]

                    processedConjugations.append({
                        'name': conjugationData.pop(0),
                        'form': conjugationData.pop(0),
                        'value': ' '.join(conjugationData),
                    })

                outputFile.write(data[0] + '|' + data[2] + '|' + json.dumps(processedConjugations) + '\r\n')
            else:
                outputFile.write(data[0] + '|' + data[2] + '|\r\n')

            index += 1
            if index % 1000 == 0:
                print(str(index) + '\r\n')