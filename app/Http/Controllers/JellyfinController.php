<?php

namespace App\Http\Controllers;

use App\Services\JellyfinService;

// request classes
use App\Http\Requests\Jellyfin\JellyfinApiCallRequest;

class JellyfinController extends Controller
{
    private $jellyfinService;

    function __construct(JellyfinService $jellyfinService) {
        $this->jellyfinService = $jellyfinService;
    }

    private function jellyfinApiCall (JellyfinApiCallRequest $request) {
        $method = $request->post('method');
        $url = $request->post('url');

        try {
            $response = $this->jellyfinService->makeRequest($method, $url);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($response, 200);
    }

    public function getJellyfinCurrentlyPlayedSubtitles () {
        try {
            $subtitles = $this->jellyfinService->getJellyfinCurrentlyPlayedSubtitles();
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($subtitles, 200);
    }
}
