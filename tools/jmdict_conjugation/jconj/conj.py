#!/usr/bin/env python3
# -*- coding: utf-8 -*-
#######################################################################
#  Copyright (c) 2014,2018 Stuart McGraw
#
#  JMdictDB is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published
#  by the Free Software Foundation; either version 2 of the License,
#  or (at your option) any later version.
#
#  JMdictDB is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with JMdictDB; if not, write to the Free Software Foundation,
#  51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
#######################################################################

import sys, os, csv, re, collections, pdb

def main ():
        args = parse_args() # Parse command line, use --help for info.
          # read the conjugation .csv files into a single data structure.
          # See read_conj_tables() for description of 'ct's structure.
        ct = read_conj_tables (args.dir)
        if args.list:
            print_help (ct); return;
          # Convert the given pos keyword into pos id number.
        try: pos = ct['kwpos'][args.pos][0]
        except KeyError:
            sys.exit ("unknown part-of-speech: %s\n'conj.py --list' will "
                "print a list of conjugatable parts-of-speech" % args.pos)
        if pos not in [x[0] for x in ct['conjo']]:
            sys.exit ("no conjugation data available for part-of-speech: %s\n"
                "'conj.py --list' will "
                "print a list of conjugatable parts-of-speech" % args.pos)
        conjs = conjugate (args.kanj, args.kana, pos, ct)
          # Some conjugations have multiple forms (e.g. ~なくて and ~ないで) that
          # are disinguished by 'onum' in the conjugation key.  The following
          # call will combine these into a single conjugation entry with a text
          # value of the individual conjugations separated by '/' in one string.
        conjs, notes = combine_onums (conjs, ct)
          # Display the conjugations.
        print_conjs (conjs, ct)
        if notes: print ("Notes:")
        for n in sorted (notes):
            print ("[%s] -- %s" % (n, ct['conotes'][n][1]))

###############################################################################
# The following two functions constitute the conjugator, everything else
# provides support only (formatting, printing, reading the data tables, etc.)

def conjugate (ktxt, rtxt, pos, ct):
        '''\
        Generate a dict containing all the conjugated forms of the kanji
        and/or kana texts 'ktxt' and 'rtxt'.

        Parameters:
          ktxt -- (str) Kanji text of the word to be conjugated.
          rtxt -- (str) Reading text of the word to be conjugated.
          pos -- (int) Id number for the part-of-speech of the word
              to be conjugated.
          ct -- Conjugation table.  This data for these are in the "data/"
              subdirectory and may be read in with read_conj_tables().

        Returns:
          A dictionary whose keys are 5-tuples:
            pos: Part-of-speech number (all the generated conjugations
                   will have the same pos value which will be the same
                   as parameter 'pos'.)
            conj: The conjugation number (an id field value from conj.id)
            neg: A bool, false for affirmative conjugation, true for negative.
            fml: A bool, false for plain, true for formal (-masu) form.
            onum: Int index (starting from one) to disambiguate conjugations
                   that have multiple forms (e.g, ～なくて and ～ないで).
          These keys are of the same form as used in the 'ct' conjugation
          table, see read_csv_files() for more details.
          The value of each item is a string with the combined conjugated
          form of 'ktxt' and 'rtxt' for that conjugation.
        '''

        conjs = {}
          # Get pos number from kw:
        for conj,conjnm in sorted (ct['conj'].values(), key=lambda x:x[0]):
            for neg, fml in (0,0),(0,1),(1,0),(1,1):
                neg, fml = bool (neg), bool (fml)
                for onum in range(1,10):  # onum values start at 1, not 0.
                    try: _,_,_,_,_, stem, okuri, euphr, euphk, _ \
                      = ct['conjo'][pos,conj,neg,fml,onum]
                    except KeyError: break;
                    kt = construct (ktxt, stem, okuri, euphr, euphk) \
                         if ktxt else ''
                    rt = construct (rtxt, stem, okuri, euphr, euphk) \
                         if rtxt else ''
                    txt = (kt + '【' + rt + '】') if kt and rt else (kt or rt)
                    conjs[(pos,conj,neg,fml,onum)] = txt
        return conjs

def construct (txt, stem, okuri, euphr, euphk):
        '''Given a word (in kanji or kana), generate its conjugated form by
        by removing removing 'stem' characters from its end (and an additional
        character if the word is kana and 'euphr' is true or the word is in
        kanji and 'euphk' are true), then appending either 'euphr' or 'euphk'.
        We determine if the word is kanji or kana by looking at its next-to-
        last character.  Finally, 'okuri' is appended.'''

        if len (txt) < 2: 
            raise ValueError ("Conjugatable words must be at least"
                              " 2 characters long")
        iskana = txt[-2] > 'あ' and txt[-2] <= 'ん'
        if iskana and euphr or not iskana and euphk: stem += 1
        if iskana: conjtxt = txt[:-stem] + (euphr or '') + okuri
        else:      conjtxt = txt[:-stem] + (euphk or '') + okuri
        return conjtxt

###############################################################################
# The remainder of this module is support functions for things like
# formatting and printing the conjugation results, reading the conjugation
# data tables, parsing the command line arguments, etc.
 
def print_conjs (conjs, ct):
        'Print the conjugation table returned by combine_onums().'
          # Create a dictionary to map combinations of 'neg' and 'fml' in the
          # 'conjs' dict keys to printable text.
        labels = {(0,0):"aff-plain:  ", (0,1):"aff-formal: ",
                  (1,0):"neg-plain:  ", (1,1):"neg-formal: "}
          # Go though all the entries in 'conjs' (each of which is a conjugation)
          # of the given kanji and kana) and print them.
        for key,txt in sorted (conjs.items()):
            pos,conj,neg,fml = key
              # Get the conjugation description from the conjugation
              #  number 'conj'.
            conjdescr = ct['conj'][conj][1]
            print ("%-20s %s %s" % (conjdescr, labels[(neg,fml)], txt))

def combine_onums (conjs, ct):
        '''Combine multiple conjugation variant "onum" forms of the same
        conjugation into an a single entry with the onum vaiant texts
        combined into a single string with "　/　" separating the forms.
        The structure of the dict returned is identical to 'conjs' except
        instead of having keys, (pos,conj,neg,fml,onum) the keys are
        (pos,conj,neg,fml).
        We also append any relevant note numbers to the text string here.'''

        newconjs = {};  allnotes = set()
        for key in sorted (conjs.keys()):
            pos,conj,neg,fml,onum = key
            txt = conjs[key]
            notes = ct['conjo_notes'][key]
            allnotes.update (notes)
            if notes: txt += '[' + ','.join([str(x) for x in notes]) + ']'
            if (pos,conj,neg,fml) not in newconjs:
                newconjs[pos,conj,neg,fml] = txt
            else:
                newconjs[pos,conj,neg,fml] += ' / ' + txt
        return newconjs, allnotes

def print_help (ct_):
        '''Print a list of the art-of-speech keywords for pos' that this
        program can conjugate.'''
          # In Python-3.3.0 we can not access the parameter 'ct_' inside
          #  the second (maybe both?) list comprehensions below (get a
          #  NameError exception -- says name is not global?!).  Accessing
          #  a function-local variable works fine.
        ct = ct_
          # Get all conjugatable pos id numbers from the main conjugations
          #  table, conjo.csv.'''
        poskws = set ([x[0] for x in ct['conjo'].values()])
          # Get a list of kwpos rows (each containing a pos id number, keyword
          #  and description text, for all the pos numbers in 'poskws'.  Sort
          #  the resulting list by keyword alphabetically.
        availpos = sorted ([ct['kwpos'][x] for x in poskws], key=lambda x:x[1])
        print ("Conjugatable PoS values:")
        for pos,poskw,descrip in availpos:
            print ("%s\t%s" % (poskw, descrip))
        #print ("Available conjugations:")
        #for conj, descrip in sorted (ct['conj'].values(), key=lambda x:x[0]):
        #    print ("%s\t%s" % (conj, descrip))

# The following functions read the .csv conjugation data.

def read_conj_tables (dir):
        '''Read the conjugation .csv files located in directory 'dir'.
        Returned is a dict whose keys are the names of each file sans
        the .csv part.  Each value is the contents of the corresponding
        csv file in the form of another dict.  The keys of each of
        these dicts are the values of the first column of the csv
        file (as converted by 'coltypes' below), except for 'conjo'
        where the key is a tuple of the first five columns.  An
        additional set of keys is added in the case of 'kwpos' which
        from the second (kw) column to allow looking up pos records
        by either id number or keyword string.
        The values of each of these dict's entries are a list of all
        the values in the csv file row (with each converted to the
        right datatype as specified by 'coltypes'.)

        Or, shown schematically:

            dict { 'conj': { 1: [1, 'Non-past'],    # Data from conj.csv...
                             2: [2, 'Past (~ta)'],
                             ... },
                   'conjo': { (1,1,False,False,1): [1,1,False,False,1,'い',None,None,None],
                               ...
                              (45,2,False,True,1): [45,2,False,True,1,'ました,','き',None,None],
                               ... },
                   'conjo_notes': { (2,1,True,False,1): [3],
                                    (2,1,True,True,1):  [3],
                                    ....
                                    (28,9,True,True,1): [5,6],
                                    ... },
                   'kwpos': { 1: [1, 'adj-i', 'adjective...'],
                              2: [2, 'adj-na', 'adjectival noun...'],
                              ...
                              'adj-i':  [1, 'adj-i', 'adjective...'],
                              'adj-na': [2, 'adj-na', 'adjectival noun...'],
                              ... },
                     ...
                     }
        '''
          # For each csv file (identified sans the .csv suffix), give a
          # list of functions, one for each column in the file, that will
          # convert the text string read into the correct data type.
          # Note that xint() is the same as int() but handles empty
          # ('') strings, sbool() converts text strings "t..." or "f..."
          # to bools.
        coltypes = {
            'conj': [int, str],
            'conjo': [int, int, sbool, sbool, int, int, str, str, str, xint],
            'conotes': [int, str],
            'conjo_notes': [int, int, sbool, sbool, int, int],
            'kwpos': [int, str, str],}
        ct = {}
        for fn in coltypes.keys():
            filename = os.path.join (dir, fn + '.csv')
            csvtbl = readcsv (filename, coltypes[fn], fn!='kwpos')
            if fn == 'conjo':
                  # Handle conjo.csv specially: add each row to its dict under
                  # the key of a 5-tuple of the first five row values.  These
                  # (pos,conj,new,fml,onum) identify the okurigana and other
                  # data needed for a specific conjugation.
                ct[fn] = dict (((tuple(row[0:5]),row) for row in csvtbl))
            elif fn == 'conjo_notes':
                  # conjo_notes maps multiple conjugations (pos,conj,neg,fml,
                  #  onum) to multiple note numbers.  So instead of using a
                  #  dictionary keyed by conjugation and where each value is
                  #  a row, we use one where each value is a list of note
                  # numbers for that conjugation.
                ct[fn] = d = collections.defaultdict (list)
                for row in csvtbl: d[tuple(row[0:5])].append (row[5])
            else:
                  # For all other csv files, add the row to the dict with a key
                  # of the first column which is an id number.
                ct[fn] = dict (((row[0],row) for row in csvtbl))
                  # Do the same to kwpos.csv but in addition add the same row
                  # with a key of the 2nd column (the kw abbr string.)  This 
                  # will allow us to look up kwpos records by either id number
                  # or keyword string.
                if fn == 'kwpos': ct[fn].update (((row[1],row) 
                                                  for row in csvtbl))
        return ct

def readcsv (filename, coltypes, hasheader):
        ''' Read the csv file 'filename', using the function in 'coltypes'
        to convert each datum to the correct datatype.  'coltypes' is indexed
        by file, and then by column number.  If 'hasheader is true, then the
        first line (containing column names) is skipped.  All the "conj*.csv
        file have headers, the "kwpos.csv" file doesn't.
        A list of rows, with each row a list of row items by column, is
        returned.'''

        table = []
        with open (filename, newline='') as f:
            reader = csv.reader(f, delimiter='\t')
            if hasheader: next (reader) # Skip header row.
            for row in reader:
                  # Apply a conversion function from 'coltypes'
                  #  to convert each datum read from the file (as
                  #  a string) to the right type (int, bool, etc).
                newrow = [coltypes[cnum](col) for cnum, col in enumerate (row)]
                table.append (newrow)
            return table

def sbool (arg):
        'Convert a string to a bool.'
        if arg.lower().startswith ('f'): return False
        if arg.lower().startswith ('t'): return True
        raise ValueError (arg)

def xint (arg):
        'Convert a string to an int or to None if blank.'
        if arg is None or arg == '': return None
        return int (arg)


from argparse import ArgumentParser

def parse_word (args):
        ''''args' is a list of one or two strings that are the kanji, kana
        arguments from the command line.  If two, we take them to be in the
        order kanji, kana.  But if one, it could be either kanji or kana
        and we identify which by looking for any kanji character (>=0x4000)
        in it.  We return separate kanji and kana strings accordingly.'''

        if len (args) == 1:
            if any ((ord(c) >= 0x4000 for c in args[0])):
                kanj,kana = args[0],None
            else:
                kanj,kana = None,args[0]
        else: kanj, kana = args
        return kanj, kana

def parse_args (argv=None):
        p = ArgumentParser (add_help=False,
            description="%prog will print a list of the conjugated forms "
                "of the Japanese word given by the kanji and/or kana words "
                "given in the ARGS argument(s).  POS is a part-of-speech "
                "code as used in wwwjdic, JMdict, etc ('v1', 'v5k', "
                "'adj-i', etc.)")
        p.add_argument ("pos", nargs='?',
            help="Part-of-speech code word as used in wwjdic, JMdict, etc.  "
                "Run program with \"--list\" to get list of valid pos values.")
        p.add_argument ("word", nargs='*',
            help="Word to be conjugated.  Either or both kanji or kana "
                "forms may be given.  If both are given, both will be "
                "conjugated, and the program will look for kanji in one "
                "to determine which is which.")
        p.add_argument ("--list", action="store_true", default=False,
            help="Print list of valid pos values to stdout and exit.")
        p.add_argument ("-d", "--dir", default='./data',
            help="Directory where the conjugation csv data files are kept.")
        p.add_argument ("--help",
            action="help", help="Print this help message.")

        args = p.parse_args (argv)
        if args.list: return args
        if not args.pos or not re.match(r'[a-z0-9-]+$', args.pos):
            p.error ("Argument 'pos' is required if --list not given.")
          # The shell won't distinguish args separated by jp space characters
          # as seperate.  But users will frequently enter jp space characters
          # to separate kanji and reading because it is pain to switch back
          # to ascii for one character.  So we split them here.
        words = []
        for w in args.word:
            ws = re.split (r'\s+', w)
            words.extend (ws)
        args.word = words
        if not 1 <= len (args.word) <= 2:
            p.error ("You must give one or two words to conjugate")
        args.kanj, args.kana = parse_word (args.word)
        return args

if __name__ == '__main__': sys.exit (main())


