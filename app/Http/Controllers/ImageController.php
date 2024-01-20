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
    
    public function getBookImage($name)
    {
        return Storage::get('/images/book_images/' . $name);
    }
}
