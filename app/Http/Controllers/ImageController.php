<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function getImage($basePath, $name, $defaultImagePath)
    {
        $extensions = ['svg', 'jpg', 'png'];

        foreach ($extensions as $extension) {
            $path = $basePath . $name . '.' . $extension;

            if (Storage::exists($path)) {
                return Storage::get($path);
            }
        }

        // If none of the specified extensions exist, return the content of the default image
        return Storage::get($defaultImagePath);
    }

    public function getFlagImage($name)
    {
        $basePath = '/images/flags/';
        $defaultImagePath = '/images/flags/default-flag.svg';

        return $this->getImage($basePath, $name, $defaultImagePath);
    }

    public function getBookImage($name)
    {
        $basePath = '/images/book_images/';
        $defaultImagePath = '/images/book_images/default.jpg';

        return $this->getImage($basePath, $name, $defaultImagePath);
    }
}
