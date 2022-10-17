conj.py -- 
A data-driven program to conjugate Japanese verbs and adjectives.

This program demonstrates a table-based, data-driven approach to 
conjugating Japanese predicates (including verbs and adjectives).

This is an example program demonstrating how the JMdictDB word
conjugation tables can be used, independently of the rest of the
JMdictDB API, to conjugate Japanese words.  JMdictDB [*1] is a
project to put the contents of Jim Breen's [*2] JMdict Japanese-
English dictionary data [*3] into a database, and provide a 
web-based maintenance system for it.

conj.py will print a table of conjugations for the word given
on the command line.  You are required to supply the word's
part-of-speech code as used in wwwjdic, JMdict, et.al, since
this program in independent of JMdictDB and has no other way
to determine it.  Run this program with "--help" for full
details on the command line arguments and options.

It would be a straight-forward exercise to change this code
to look the word up in a JMdict database to get the part-
of-speech value automatically or to format the output more
nicely, etc.

#-------------------------------------------------------------------

JMdictDB uses a set of four csv (comma separated value) tables
that contain information needed to conjugate a japanese word
based on the word's part-of-speech code (e.g., v1 verb, v5k verb,
i-adjective, etc).  In JMdictDB these data are loaded into database
tables and the word conjugation done via SQL views.

However it is also possible to using read these csv files directly
in an application program and do the conjugation in code; this
program is an example of that.

This program conjugates words using primarily the conjugation
data in file .../conjo.csv.  That file consists of rows of 10
values per row:

   pos:   A part-of-speech number.  These are defined in kwpos.csv
          and each number corresponds to a keyword like 'v1', 'v5k'
          'n', 'vs, adj-i', etc, as used in wwwjdict, JMdict.xml,
          etc.
   conj:  A conjugation number.  These are defined in conj.csv.
   neg:   If false, this row is for the affirmative conjugation
          form.  If true, for the negative form.
   fml:   If false, this row is for the plain conjugation form.
          If true, for the formal (aka polite) form.
   onum:  A disambiguating number (starting from 1) for rows
          containing variant okurigana for the same conjugation
          (e.g., ～なくて and ～ないで).

   stem:  The number of characters to remove from the end of a
          word before adding okurigana.
   okuri: The okurigana text for the conjugated form.
   euphr: Replacement kana text for the verb stem in cases where
          there is a euphonic change in the stem (e.g. く -> こ in
          来る -> 来ない).
   euphk: Replacement kanji text when there is a kanji change in
          the stem.  (The only case of this is for the Potential
          conjugation form of suru: 為る・する -> 出来る・できる).
   pos2:  Not currently used.

The first five items form a unique key identifying each
conjugation and are used as a key in the python dicts in the
code below to identify conjugations.  The remaining items in
each row are used to construct the conjugated form of a word
for the conjugation specified by the key.

The algorithm for conjugating a word is: 
1. For each (or a desired) conjugation (identified by the
   tuple (pos,conj,neg,fml,onum)), get stem, okuri, euphr
   and euphk from conjo.csv.
2. If euphr or euphk is non-null, determine if the word to
   be conjugated is kanji or kana.  In the general case there
   is no easy way of doing this since it is hard to tell how
   long the word is if it occurs at the end of a longer phrase.
   But since the only PoS values that have euphr or euphk values
   currently are for the words: いい,　来る・くる and 為る・する we
   can look at the next-to-last character to see if it is kanji
   or kana.
3. Conjugate the word:
3a. If euphr and euphk are null, remove 'stem' characters from
   the end of the text.
3b. Else if 'euphk' is not null and the word is kanji, remove
   stem+1 characters from the end of the text and append 'euphk'
   to the end of the text. 
3c. Else if 'euphr' is not null and the word is kana, remove
   stem+1 characters from the end of the text and append 'euphr'
   to the end of the text. 
3d. Append 'okuri' to the end of the text.

(See function construct() below.)

The other csv tables are:
   conj.csv: Maps each conj number to a descriptive text.
   conotes.csv: A set of test notes, each identified by an
        id number.
   conjo_notes.csv: Maps notes in conotes.csv (identified
        by id number) to conjugations in conjo.csv
        (identified by (pos,conj,neg,fml,onum)).

This program reads the csv files because JMdictDB requires the
conjugation data in that form to load into database tables.  But
it should be clear that the conjugation data could alternatively
be included in a program in the form of constant, embedded
tables, possibly in a more conveniently structured form such as
is currently returned by read_conj_tables(), rather than reading
the csv files each time.  One could also eliminate the pos numbers,
substituting the corresponding abbreviation string wherever the
numbers occur.

See also:
pg/mkviews.sql -- contains SQL views that perform the same
operations as this program using the .csv data read into
database tables.  Those views (primarily "vinfl" around
line 670) provide the conjugated words for the web/cgi/conj.py
JMdictDB web page.

======================================================================
Notes:
[*1] http://edrdg.org/~smg/
     https://gitlab.com/yamagoya/jmdictdb/
[*2] http://nihongo.monash.edu/Japanese.html
[*3] http://www.edrdg.org/wiki/index.php/JMdict-EDICT_Dictionary_Project
