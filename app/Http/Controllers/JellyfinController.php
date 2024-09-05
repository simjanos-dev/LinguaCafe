<?php

namespace App\Http\Controllers;

use App\Services\JellyfinService;

class JellyfinController extends Controller
{
    private $jellyfinService;

    function __construct(JellyfinService $jellyfinService) {
        $this->jellyfinService = $jellyfinService;
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
