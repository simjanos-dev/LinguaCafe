<?php

namespace App\Services\WordImages;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class WordImageSearchService 
{
    private $maxTries;
    private $delayBetweenTries;

    public function __construct() 
    {
        $this->maxTries = 10;
        $this->delayBetweenTries = 1000;
    }

    public function search(string $searchTerm) : Collection
    {
        $bingUrl = "https://www.bing.com/images/search";
        $searchUrl = $bingUrl . "?q=" . urlencode($searchTerm);

        $imageSearchResponse = $this->makeBingRequests($searchUrl);

        if (!$this->validateResponse($imageSearchResponse)) {
            throw new \Exception('Bing search has returned an empty page.');
        }

        $imageSearchDom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $imageSearchDom->loadHTML($imageSearchResponse->body());
        libxml_clear_errors();

        $xpath = new \DOMXPath($imageSearchDom);
        $nodes = $xpath->query('//div[@class="imgpt"]/a/@m');

        $urls = [];
        foreach($nodes as $node) {
            $jsonObject = json_decode($node->nodeValue);

            if ($jsonObject === null) {
                continue;
            }

            $urls[] = [
                'small' => $jsonObject->turl,
                'original' => $jsonObject->murl,
            ];
        }


        return collect($urls)->take(20);
    }

    private function makeBingRequests(string $url): Response
    {
        $retries = 0;
        $response = null;

        while ($retries < $this->maxTries && ($retries === 0 || !$this->validateResponse($response))) {
            $response = Http::get($url);
            $retries ++;

            if (!$this->validateResponse($response)) {
                usleep($this->delayBetweenTries * 1000);
            }
        }

        return $response;
    }

    private function validateResponse(null|Response $response): bool
    {
        if ($response === null) {
            return false;
        }

        if(!$response->ok()) {
            return false;
        }

        if(!strlen($response->body())) {
            return false;
        }

        return true;
    }
}