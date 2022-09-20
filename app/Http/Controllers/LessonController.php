<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EncounteredWord;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Phrase;
use App\Models\DailyAchivement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function courses() {
        $selectedLanguage = Auth::user()->selected_language;
        $courses = Course::where('language', $selectedLanguage)->where('user_id', Auth::user()->id)->orderBy('updated_at', 'DESC')->get();

        return view('courses', [
            'courses' => $courses,
            'language' => $selectedLanguage
        ]);
    }

    public function getCreateCourse() {
        return view('create_course');
    }

    public function postCreateCourse(Request $request) {
        $course = new Course();
        $course->user_id = Auth::user()->id;
        $course->name = $request->name;
        $course->cover_image = '';
        $course->language = Auth::user()->selected_language;
        $course->save();

        if (!is_null($request->cover_image)) {
            // save image on server
            $fileName = $course->id . '.' . ($request->file('cover_image')->getClientOriginalExtension());
            $path = $request->file('cover_image')->storeAs('/images/course_images/', $fileName);

            // save image in database
            $course->cover_image = $fileName;
            $course->save();
        }

        return redirect('/courses');
    }

    public function updateWordCounts($courseId = -1) {
        $selectedLanguage = Auth::user()->selected_language;
        $encounteredWords = EncounteredWord::select(['word', 'stage'])->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        
        if ($courseId !== -1) {
            $courses = Course::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('id', $courseId)->get();
        } else {
            $courses = Course::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        }

        foreach ($courses as $course) {
            $lessons = Lesson::where('user_id', Auth::user()->id)->where('course_id', $course->id)->get();

            $uniqueWords = [];
            $uniquePhraseIds = [];
            $wordCount = 0;
            $knownWordCount = 0;
            $highlightedWordCount = 0;
            $newWordCount = 0;

            foreach ($lessons as $lesson) {
                $lessonKnownWordCount = 0;
                $lessonHighlightedWordCount = 0;
                $lessonNewWordCount = 0;
                $lessonUniqueWords = json_decode($lesson->unique_words);
                $lessonUniqueWords = EncounteredWord::select(['word', 'stage'])->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereIn('word', $lessonUniqueWords)->get();
                $lessonUniquePhraseIds = [];
                $words = json_decode(gzuncompress($lesson->processed_text));
                
                // check for unique phrase id
                foreach($words as $word) {
                    foreach ($word->phraseIds as $phraseId) {
                        if (!in_array($phraseId, $lessonUniquePhraseIds)) {
                            array_push($lessonUniquePhraseIds, $phraseId);
                        }

                        if (!in_array($phraseId, $uniquePhraseIds)) {
                            array_push($uniquePhraseIds, $phraseId);
                        }
                    }
                }

                // check for unique word
                foreach ($lessonUniqueWords as $word) {
                    if (!array_search(mb_strtolower($word->word), $uniqueWords, true)) {
                        array_push($uniqueWords, mb_strtolower($word->word));

                        if ($word->stage < 0) {
                            $highlightedWordCount ++;
                        }

                        if ($word->stage == 0) {
                            $knownWordCount ++;
                        }

                        if ($word->stage == 2) {
                            $newWordCount ++;
                        }
                    }

                    if ($word->stage < 0) {
                        $lessonHighlightedWordCount ++;
                    }

                    if ($word->stage == 0) {
                        $lessonKnownWordCount ++;
                    }

                    if ($word->stage == 2) {
                        $lessonNewWordCount ++;
                    }
                }

                $lesson->known_word_count = $lessonKnownWordCount;
                $lesson->highlighted_word_count = $lessonHighlightedWordCount + count($lessonUniquePhraseIds);
                $lesson->new_word_count = $lessonNewWordCount;
                $lesson->save();
                $wordCount += $lesson->word_count;
            }

            $course->unique_word_count = count($uniqueWords);
            $course->word_count = $wordCount;
            $course->known_word_count = $knownWordCount;
            $course->highlighted_word_count = $highlightedWordCount +  + count($uniquePhraseIds);
            $course->new_word_count = $newWordCount;
            $course->touch();
            $course->save();
        }
    }

    public function lessons($courseId) {
        $course = Course::where('id', $courseId)->where('user_id', Auth::user()->id)->first();
        $lessons = Lesson::where('course_id', $courseId)->where('user_id', Auth::user()->id)->get();
        
        return view('lessons', [
            'course' => $course,
            'lessons' => $lessons
        ]);
    }

    public function lesson($lessonId) 
    {        
        $wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
            '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
            '«', '»', "'", '’', '–', 'NEWLINE'];
        $selectedLanguage = Auth::user()->selected_language;
        

        $lesson = Lesson::where('id', $lessonId)->where('user_id', Auth::user()->id)->first();
        $uniqueWords = json_decode($lesson->unique_words);
        $course = Course::where('id', $lesson->course_id)->where('user_id', Auth::user()->id)->first();
        $lessons = Lesson::select(['id', 'name', 'read_count'])->where('course_id', $course->id)->where('user_id', Auth::user()->id)->get();
        $encounteredWords = DB::table('encountered_words')->select(DB::raw('*, false as checked, 0 as appearance_in_text'))->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereIn('word', $uniqueWords)->get();
        $words = json_decode(gzuncompress($lesson->processed_text));

        // get unique phrase ids
        $phraseIds = [];
        for ($wordCounter = 0; $wordCounter < count($words); $wordCounter ++) {        
            for ($phraseCounter = 0; $phraseCounter < count($words[$wordCounter]->phraseIds); $phraseCounter ++) {
                if (!in_array($words[$wordCounter]->phraseIds[$phraseCounter], $phraseIds)) {
                    array_push($phraseIds, $words[$wordCounter]->phraseIds[$phraseCounter]);
                }
            }
        }

        sort($phraseIds);
        // get unique words from lesson
        for ($wordCounter = 0; $wordCounter < count($words); $wordCounter ++) {
            // make the word into an object
            $word = $words[$wordCounter];
            $word->selected = false;
            $word->hover = false;
            $word->phraseStage = 'learning';
            $word->phraseStart = false;
            $word->phraseEnd = false;
            $word->phraseIndexes = [];

            // replace phrase ids with phrase indexes
            foreach($word->phraseIds as $phraseIndex => $phraseId) {
                $index = array_search($phraseId, $phraseIds);
                array_push($word->phraseIndexes, $index);
            }

            $wordId = $encounteredWords->search(function ($item, $key) use($word) {
                return $item->word == mb_strtolower($word->word);
            });

            if ($wordId !== false) {
                $word->stage = $encounteredWords[$wordId]->stage;
                $word->lookup_count = $encounteredWords[$wordId]->lookup_count;
                $word->last_level_up = $encounteredWords[$wordId]->last_level_up;
                $encounteredWords[$wordId]->read_count ++;
            } else {
                $word->stage = 2;
                $word->lookup_count = 0;
                $word->last_level_up = '';
            }

            $words[$wordCounter] = $word;
        }

        $lesson->processed_text = json_encode($words);
        $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereIn('id', $phraseIds)->orderBy('id')->get();
        for ($i = 0; $i < count($phrases); $i++) {
            $phrases[$i]->words = json_decode($phrases[$i]->words);
            $phrases[$i]->checked = false;
        }

        return view('lesson', [
            'lesson' => $lesson,
            'course' => $course,
            'uniqueWords' => $encounteredWords,
            'lessons' => $lessons,
            'phrases' => $phrases,
        ]);
    }

    public function finishLesson(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $uniqueWords = json_decode($request->uniqueWords);
        $phrases = json_decode($request->phrases);
        $deletedPhrases = json_decode($request->deletedPhrases);
        $today = date('Y-m-d');
        $encounteredWordsRequest = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->select('word')->get();
        $encounteredWordList = [];

        foreach ($encounteredWordsRequest as $word) {
            array_push($encounteredWordList, $word->word);
        }
        
        // save newly encountered words and edited translations
        foreach ($uniqueWords as $uniqueWordData) {
            if (in_array($uniqueWordData->word, $encounteredWordList, true)) {
                $encounteredWord = EncounteredWord::where('word', $uniqueWordData->word)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->first();
                $encounteredWord->stage = $uniqueWordData->stage;

                // increase word stage strength if it wasn't checked
                if ($encounteredWord->stage < 0 && !$uniqueWordData->checked && 
                    $uniqueWordData->last_level_up !== $today) {
                    $encounteredWord->stage++;
                    $encounteredWord->last_level_up = $today;
                }

                // these are words that the user sees the first time in the software,
                // but they already know it
                if ($encounteredWord->stage == 2) {
                    $encounteredWord->stage = 0;
                    $encounteredWord->last_level_up = $today;
                }

                $encounteredWord->translation = $uniqueWordData->translation;
                $encounteredWord->reading = $uniqueWordData->reading;
                $encounteredWord->base_word = $uniqueWordData->base_word;
                $encounteredWord->base_word_reading = $uniqueWordData->base_word_reading;
                $encounteredWord->example_sentence = $uniqueWordData->example_sentence;
                $encounteredWord->lookup_count = $uniqueWordData->lookup_count;
                $encounteredWord->read_count = $uniqueWordData->read_count;
                $encounteredWord->save();
            }
        }

        // increase lesson read count
        $lesson = Lesson::where('id', $request->lessonId)->where('user_id', Auth::user()->id)->first();
        $lesson->read_count ++;
        $lesson->save();

        // save phrases
        foreach ($phrases as $currentPhrase) {
            $newPhrase = false;
            if ($currentPhrase->id == -1) {
                $newPhrase = true;
                $phrase = new Phrase();
                $phrase->user_id = Auth::user()->id;
                $phrase->language = $selectedLanguage;
            } else {
                $phrase = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('id', $currentPhrase->id)->first();
            }

            $phrase->words = json_encode($currentPhrase->words);
            $phrase->stage = intval($currentPhrase->stage);
            $phrase->reading = $currentPhrase->reading;
            $phrase->translation = $currentPhrase->translation;            
            if ($phrase->stage < 0 && !$currentPhrase->checked && 
                $phrase->last_level_up !== $today) {
                $phrase->stage++;
                $phrase->last_level_up = $today;
            }

            $phrase->save();

            // if this phrase was not in the database before, 
            // mark the phrase in all text
            if ($newPhrase) {
                $lessons = Lesson::all();
                foreach ($lessons as $currentLesson) {
                    $currentLesson->updatePhraseIds($phrase->id);
                }
            }
        }

        // delete phrases
        foreach ($deletedPhrases as $currentPhrase) {
            $lessons = Lesson::all();
            foreach ($lessons as $currentLesson) {
                $currentLesson->deletePhraseIds($currentPhrase->id);
            }
            
            Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('id', $currentPhrase->id)->delete();
        }

        // increase read word count
        $dailyAchivement = DailyAchivement::where('user_id', Auth::user()->id)->where('day', \date('Y-m-d'))->where('language', $selectedLanguage)->first();
        if (!$dailyAchivement) {
            $dailyAchivement = new DailyAchivement();
            $dailyAchivement->user_id = Auth::user()->id;
            $dailyAchivement->day = \date('Y-m-d');
            $dailyAchivement->read_words = 0;
            $dailyAchivement->reviewed_words = 0;
            $dailyAchivement->language = $request->language;
        }

        $dailyAchivement->read_words += $lesson->word_count;
        $dailyAchivement->save();
        shell_exec('./../tokenizer.py');
        $this->updateWordCounts();
    }

    public function createLesson($courseId) {
        $course = Course::where('id', $courseId)->where('user_id', Auth::user()->id)->first();

        return view('edit_lesson', [
            'course' => $course
        ]);
    }

    function editLesson($lessonId) {
        $lesson = Lesson::where('user_id', Auth::user()->id)->where('id', $lessonId)->first();
        $course = Course::where('user_id', Auth::user()->id)->where('id', $lesson->course_id)->first();

        return view('edit_lesson', [
            'course' => $course,
            'lesson' => $lesson
        ]);
    }

    public function saveLesson(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $kanjipattern = "/[a-zA-Z0-9０-９あ-んア-ンー。、:？！＜＞： 「」（）｛｝≪≫〈〉《》【】
            『』〔〕［］・\n\r\t\s\(\)　]/u";

        if (isset($request->lesson_id)) {
            $lesson = Lesson::where('id', $request->lesson_id)->where('user_id', Auth::user()->id)->first();
        } else {
            $lesson = new Lesson();
        }
        
        $lesson->user_id = Auth::user()->id;
        $lesson->name = $request->name;
        $lesson->read_count = isset($request->lesson_id) ? $lesson->read_count : 0;
        $lesson->word_count = 0;
        $lesson->unique_word_count = 0;
        $lesson->course_id = $request->course;
        $lesson->language = $selectedLanguage;
        $lesson->raw_text = str_replace("\r\n", " NEWLINE \r\n", $request->raw_text);
        $lesson->processed_text = '';
        $lesson->unique_words = '';
        $lesson->save();
        
        shell_exec('./../tokenizer.py');

        $lesson = $lesson->find($lesson->id);
        $words = json_decode($lesson->processed_text);

        $wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
                        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
                        '«', '»', "'", '’', '–', 'NEWLINE'];
        $wordCount = 0;
        $uniqueWords = [];
        $uniqueWordIds = [];

        for ($i = 0; $i < count($words); $i++) {
            $word = new \StdClass();
            $word->word = $words[$i]->word;
            $word->sentenceIndex = $words[$i]->sentenceIndex;
            $word->phraseIds = [];
            
            if (!in_array($words[$i]->word, $wordsToSkip, true)) {
                $wordCount ++;
            }
            
            if (!in_array(mb_strtolower($words[$i]->word), $uniqueWords, true)) {
                array_push($uniqueWords, mb_strtolower($words[$i]->word, 'UTF-8'));
            }

            $words[$i] = $word;
            $encounteredWord = EncounteredWord::where('word', mb_strtolower($words[$i]->word, 'UTF-8'))->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->first();
            if (!$encounteredWord) {
                if ($selectedLanguage == 'japanese') {
                    $kanji = preg_replace($kanjipattern, "", $words[$i]->word);
                    $kanji = preg_split("//u", $kanji, -1, PREG_SPLIT_NO_EMPTY);
                }

                $encounteredWord = new EncounteredWord();
                $encounteredWord->user_id = Auth::user()->id;
                $encounteredWord->language = $selectedLanguage;
                $encounteredWord->word = mb_strtolower($words[$i]->word, 'UTF-8');
                $encounteredWord->base_word = '';
                $encounteredWord->kanji = $selectedLanguage == 'japanese' ? implode('', $kanji) : '';
                $encounteredWord->reading = '';
                $encounteredWord->base_word_reading = '';
                $encounteredWord->example_sentence = '';
                $encounteredWord->stage = 2;
                $encounteredWord->translation = '';
                $encounteredWord->save();
            }

            if (!in_array($encounteredWord->id, $uniqueWordIds, true)) {
                array_push($uniqueWordIds, $encounteredWord->id);
            }
        }

        $lesson->word_count = $wordCount;
        $lesson->processed_text = gzcompress(json_encode($words), 1);
        $lesson->unique_words = json_encode($uniqueWords);
        $lesson->unique_word_count = count($uniqueWords);
        $lesson->save();

        shell_exec('./../tokenizer.py');
        
        $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        foreach($phrases as $phrase) {
            $lesson->updatePhraseIds($phrase->id);
        }

        $this->updateWordCounts($lesson->course_id);
        if (isset($request->lesson_id)) {
            return redirect('/lessons/' . $request->course);
        } else {
            return redirect('/lesson/' . $lesson->id);
        }
    }

    function deleteLesson($lessonId) {
        $lesson = Lesson::Where('id', $lessonId)->where('user_id', Auth::user()->id)->first();
        $courseId = $lesson->course_id;
        $lesson->delete();

        return redirect('/lessons/' . $courseId);
    }
}