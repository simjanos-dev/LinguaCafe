<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TextBlock;
use App\Models\Phrase;
use App\Models\MediaPlayerCache;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MediaPlayerController extends Controller
{
    // production
    private $apiKey = '0000';
    private $apiHost = 'http://jellyfin:8096';

    private $languageShorthands = [
        'jpn' => 'japanese',
        'eng' => 'english'
    ];

    function __construct() {
        // retrieve api key and host from database
        $setting = Setting::where('name', 'jellyfinApiKey')->first();
        $this->apiKey = json_decode($setting->value);
        $setting = Setting::where('name', 'jellyfinHost')->first();
        $this->apiHost = json_decode($setting->value);
    }

    /*
        Makes a request to the jellyfin api and returns the response.
    */
    private function makeJellyfinRequest ($method, $url) {
        $response = '';

        if ($method == 'GET') {
            $response = Http::withHeaders([
                'Authorization' => 'MediaBrowser Token="' . $this->apiKey . '", Client="LinguaCafe", Device="Test", DeviceId="asdsafwafaw", Version="0.1"'
            ])->get($this->apiHost . $url);
        }

        if ($method == 'POST') {
            $response = Http::withHeaders([
                'Authorization' => 'MediaBrowser Token="' . $this->apiKey . '", Client="LinguaCafe", Device="Test", DeviceId="asdsafwafaw", Version="0.1"'
            ])->post($this->apiHost . $url);
        }

        return $response->json();
    }

    /*
        Makes a jellyfin api call.
    */
    public function jellyfinRequest (Request $request) {
        return $this->makeJellyfinRequest($request->method, $request->url);
    }
    
    /*
        Returns a list of subtitles of the media currently being played
        on the jellyfin server.
    */
    public function getJellyfinCurrentlyPlayedSubtitles () {
        $calculatedSessions = [];
        $sessions = $this->makeJellyfinRequest('GET', '/Sessions');
        for ($sessionCounter = 0; $sessionCounter < count($sessions); $sessionCounter++) {
            if (!array_key_exists("NowPlayingItem", $sessions[$sessionCounter])) {
                continue;
            }

            if ($sessions[$sessionCounter]['NowPlayingItem']['MediaType'] !== 'Video') {
                continue;
            }

            $session = new \stdClass();
            $session->client = $sessions[$sessionCounter]['Client'];
            $session->userName = $sessions[$sessionCounter]['UserName'];
            $session->userId = $sessions[$sessionCounter]['NowPlayingItem']['Id'];
            $session->title = $sessions[$sessionCounter]['NowPlayingItem']['Name'];
            $session->seriesName = $sessions[$sessionCounter]['NowPlayingItem']['SeriesName'];
            $session->seriesEpisode = $sessions[$sessionCounter]['NowPlayingItem']['IndexNumber'];
            $session->seriesSeason = str_replace('Season ', '', $sessions[$sessionCounter]['NowPlayingItem']['SeasonName']);
            $session->runTimeTicks = $sessions[$sessionCounter]['NowPlayingItem']['RunTimeTicks'];
            $session->nowPlayingItemId = $sessions[$sessionCounter]['NowPlayingItem']['Id'];
            $session->mediaSourceId = $sessions[$sessionCounter]['PlayState']['MediaSourceId'];
            $session->sessionId = $sessions[$sessionCounter]['Id'];
            $session->subtitles = [];

            $calculatedSessions[] = $session;

            $mediaSource = $this->makeJellyfinRequest('GET', '/Items/' . $session->nowPlayingItemId . '/PlaybackInfo?userId=' . $session->userId);
            $mediaSource = $mediaSource['MediaSources'][0];

            for ($subtitleCounter = 0; $subtitleCounter < count($mediaSource['MediaStreams']); $subtitleCounter++) {
                if ($mediaSource['MediaStreams'][$subtitleCounter]['Type'] !== 'Subtitle' ||
                    !$mediaSource['MediaStreams'][$subtitleCounter]['IsExternal']) {
                    continue;
                }

                $subtitleText = $this->makeJellyfinRequest('GET', '/Videos/' . $session->nowPlayingItemId . '/' . $session->mediaSourceId . '/Subtitles/ ' . $mediaSource['MediaStreams'][$subtitleCounter]['Index'] . ' /Stream.js');
                
                if (array_key_exists($mediaSource['MediaStreams'][$subtitleCounter]['Language'], $this->languageShorthands)) {
                    $language = $this->languageShorthands[$mediaSource['MediaStreams'][$subtitleCounter]['Language']];
                    
                    $subtitle = new \stdClass();
                    $subtitle->language = $language;
                    $subtitle->text = $subtitleText['TrackEvents'];

                    $calculatedSessions[count($calculatedSessions) - 1]->subtitles[] = $subtitle;
                }
            }
        }

        return json_encode($calculatedSessions);
    }

    /*
        This function takes an object of data retrieved from jellyfin API,
        creates "encoutered_words" records in the database for words that
        are new for the user, then returns an object that is usable for 
        TextBlockGroup vue component. 
    */
    public function processJellyfinSubtitle(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $subtitles = $request->subtitle;
        $processedSubtitles = [];
        
        // create hash and check if subtitle exists in cache
        $hash = md5(json_encode($subtitles));
        $cachedSubtitle = MediaPlayerCache
            ::where('hash', $hash)
            ->first();
        
        
        // tokenize subtitles if it's not cached yet
        // otherwise decode cached json subtitle
        $tokenizedSubtitles = [];
        if (!$cachedSubtitle) {
            foreach ($subtitles as $subtitle) {
                $tokenizedSubtitles[] = $subtitle['Text'];
            }

            $tokenizedSubtitles = TextBlock::tokenizeRawTextArray($tokenizedSubtitles, $selectedLanguage);
        } else {
            $cachedSubtitle->subtitles = json_decode($cachedSubtitle->subtitles);
            // echo('<pre>');var_dump($cachedSubtitle->subtitles);echo('</pre>');exit;
        }
        
        // $start = microtime(true);
        // echo('time to tokenize:' . (microtime(true) - $start));exit;
        
        $phrases = Phrase
                ::where('user_id', Auth::user()->id)
                ->where('language', $selectedLanguage)
                ->get();

        $processedSubtitlesForCache = [];
        foreach ($subtitles as $subtitleIndex => $subtitle) {
            $textBlock = new TextBlock();
            
            if ($cachedSubtitle) {
                $textBlock->setProcessedWords($cachedSubtitle->subtitles[$subtitleIndex]);
            } else {
                $textBlock->tokenizedWords = $tokenizedSubtitles[$subtitleIndex];
                $textBlock->processTokenizedWords();
            }
            
            // if there is no cache yet, collect processed words for cache
            if (!$cachedSubtitle) {
                $processedSubtitlesForCache[] = $textBlock->processedWords;
            }

            $textBlock->collectUniqueWords();
            $textBlock->updateAllPhraseIds($phrases);
            $textBlock->createNewEncounteredWords();
            $textBlock->prepareTextForReader();
            $textBlock->indexPhrases();

            $startTime = $subtitle['StartPositionTicks'] / 1000/ 1000 / 10;
            $endTime = $subtitle['EndPositionTicks'] / 1000 / 1000 / 10;

            $processedSubtitles[] = $textBlock->getReaderData();
            $processedSubtitles[count($processedSubtitles) - 1]->start = $subtitle['StartPositionTicks'];
            $processedSubtitles[count($processedSubtitles) - 1]->end = $subtitle['EndPositionTicks'];
            $processedSubtitles[count($processedSubtitles) - 1]->startText = str_pad(intval($startTime / 60), 2, "0", STR_PAD_LEFT) . ':' . str_pad(intval($startTime % 60), 2, "0", STR_PAD_LEFT);
            $processedSubtitles[count($processedSubtitles) - 1]->endText = str_pad(intval($endTime / 60), 2, "0", STR_PAD_LEFT) . ':' . str_pad(intval($endTime % 60), 2, "0", STR_PAD_LEFT);
            $processedSubtitles[count($processedSubtitles) - 1]->id = $subtitleIndex;
        }

        // save cache
        if (!$cachedSubtitle) {
            $cachedSubtitle = new MediaPlayerCache();
            $cachedSubtitle->hash = $hash;
            $cachedSubtitle->subtitles = json_encode($processedSubtitlesForCache);
            $cachedSubtitle->save();
        }

        return json_encode($processedSubtitles);
    }
}
