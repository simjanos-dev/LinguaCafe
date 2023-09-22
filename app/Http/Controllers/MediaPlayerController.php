<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TextBlock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MediaPlayerController extends Controller
{
    // production
    // private $apiKey = '38c9c693ceb84535b15acdfe78c04c0e';
    // private $jellyfinUrl = 'http://jellyfin:8096';

    // dev
    private $apiKey = 'b988de56db3f4e40a13d21442a36bc28';
    private $jellyfinUrl = 'http://jellyfin-test:8096';

    private $languageShorthands = [
        'jpn' => 'japanese',
        'eng' => 'english'
    ];

    private function makeJellyfinRequest ($method, $url) {
        $response = '';

        if ($method == 'GET') {
            $response = Http::withHeaders([
                'Authorization' => 'MediaBrowser Token="' . $this->apiKey . '", Client="LinguaCafe", Device="Test", DeviceId="asdsafwafaw", Version="0.1"'
            ])->get($this->jellyfinUrl . $url);
        }

        if ($method == 'POST') {
            $response = Http::withHeaders([
                'Authorization' => 'MediaBrowser Token="' . $this->apiKey . '", Client="LinguaCafe", Device="Test", DeviceId="asdsafwafaw", Version="0.1"'
            ])->post($this->jellyfinUrl . $url);
        }

        return $response->json();
    }

    public function jellyfinRequest (Request $request) {
        return $this->makeJellyfinRequest($request->method, $request->url);
    }
    
    public function getJellyfinCurrentlyPlayedSubtitles () {
        $calculatedSessions = [];
        $sessions = $this->makeJellyfinRequest('GET', '/Sessions');
        for ($sessionCounter = 0; $sessionCounter < count($sessions); $sessionCounter++) {
            if (!array_key_exists("NowPlayingItem", $sessions[$sessionCounter])) {
                continue;
            }

            $session = new \stdClass();
            $session->client = $sessions[$sessionCounter]['Client'];
            $session->userName = $sessions[$sessionCounter]['UserName'];
            $session->userId = $sessions[$sessionCounter]['NowPlayingItem']['Id'];
            $session->title = $sessions[$sessionCounter]['NowPlayingItem']['Name'];
            $session->seriesName = $sessions[$sessionCounter]['NowPlayingItem']['SeriesName'];
            $session->episode = $sessions[$sessionCounter]['NowPlayingItem']['IndexNumber'];
            $session->nowPlayingItemId = $sessions[$sessionCounter]['NowPlayingItem']['Id'];
            $session->mediaSourceId = $sessions[$sessionCounter]['PlayState']['MediaSourceId'];
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
        are new for the user, and returns an object that is usable for 
        TextBlockGroup vue component. 
    */
    public function processJellyfinSubtitle(Request $request) {
        $subtitles = $request->subtitle;
        $processedSubtitles = [];
        

        foreach ($subtitles as $subtitleIndex => $subtitle) {
            $textBlock = new TextBlock();
            $textBlock->rawText = $subtitle['Text'];
            $textBlock->tokenizeRawText();
            $textBlock->processTokenizedWords();
            $textBlock->collectUniqueWords();
            $textBlock->updateAllPhraseIds();
            $textBlock->createNewEncounteredWords();
            $textBlock->prepareTextForReader();
            $textBlock->indexPhrases();
            // $textBlockObject->start = $subtitle['StartPositionTicks'] / 1000/ 1000 / 10;
            // $textBlockObject->end = $subtitle['EndPositionTicks'] / 1000 / 1000 / 10;

            
            
            $processedSubtitles[] = $textBlock->getReaderData();
            $processedSubtitles[count($processedSubtitles) - 1]->id = $subtitleIndex;
        }

        return json_encode($processedSubtitles);
    }
}
