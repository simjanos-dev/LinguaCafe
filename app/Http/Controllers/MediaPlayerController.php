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

    private $jellyfinLanguageCodes = [];

    function __construct() {
        $this->jellyfinLanguageCodes = config('linguacafe.languages.jellyfin_language_codes');

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
        $selectedLanguage = Auth::user()->selected_language;

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
            $session->type = $sessions[$sessionCounter]['NowPlayingItem']['Type'];

            // add movie name or series info
            if ($session->type == 'Episode') {
                $session->seriesName = $sessions[$sessionCounter]['NowPlayingItem']['SeriesName'];
                $session->seriesEpisode = $sessions[$sessionCounter]['NowPlayingItem']['IndexNumber'];
                $session->seriesSeason = str_replace('Season ', '', $sessions[$sessionCounter]['NowPlayingItem']['SeasonName']);
            } else {
                $session->movieName = $sessions[$sessionCounter]['NowPlayingItem']['Name'];
            }
            
            $session->runTimeTicks = $sessions[$sessionCounter]['NowPlayingItem']['RunTimeTicks'];
            $session->nowPlayingItemId = $sessions[$sessionCounter]['NowPlayingItem']['Id'];
            $session->sessionId = $sessions[$sessionCounter]['Id'];
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

                $subtitleText = $this->makeJellyfinRequest('GET', '/Videos/' . $session->nowPlayingItemId . '/' . $session->mediaSourceId . '/Subtitles/ ' . $mediaSource['MediaStreams'][$subtitleCounter]['Index'] . '/0/Stream.js');
                
                // add language for subtitles that Jellyfin did not recognise
                if (!isset($mediaSource['MediaStreams'][$subtitleCounter]['Language'])) {
                    $mediaSource['MediaStreams'][$subtitleCounter]['Language'] = 'unrecognised by jellyfin: ' . $mediaSource['MediaStreams'][$subtitleCounter]['Title'];
                }
                
                // retrieve language. if not possible, use the jellyfin language code instead,
                // so it can be viewed as an error message in the console and added to 
                // jellyfinLanguageCodes.
                if (array_key_exists($mediaSource['MediaStreams'][$subtitleCounter]['Language'], $this->jellyfinLanguageCodes)) {
                    $language = $this->jellyfinLanguageCodes[$mediaSource['MediaStreams'][$subtitleCounter]['Language']];
                    $supportedLanguage = true;
                } else {
                    $language = $mediaSource['MediaStreams'][$subtitleCounter]['Language'];
                    $supportedLanguage = false;
                }
                
                $subtitle = new \stdClass();
                $subtitle->language = $language;
                $subtitle->supportedLanguage = $supportedLanguage;
                $subtitle->text = $subtitleText['TrackEvents'];

                $calculatedSessions[count($calculatedSessions) - 1]->subtitles[] = $subtitle;
            }
        }

        return json_encode($calculatedSessions);
    }
}
