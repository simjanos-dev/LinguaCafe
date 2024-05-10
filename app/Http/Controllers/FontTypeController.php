<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Services\FontTypeService;

// request classes
use App\Http\Requests\FontTypes\UploadFontTypeRequest;
use App\Http\Requests\FontTypes\UpdateFontTypeRequest;
use App\Http\Requests\FontTypes\DeleteFontTypeRequest;
use App\Http\Requests\FontTypes\GetFontTypesForLanguageRequest;
use App\Http\Requests\FontTypes\GetFontTypeFileRequest;

class FontTypeController extends Controller {
    private $fontTypeService;

    public function __construct(FontTypeService $fontTypeService) {
        $this->fontTypeService = $fontTypeService;
    }

    public function getInstalledFontTypes() {
        try {
            $fonts = $this->fontTypeService->getInstalledFontTypes();
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($fonts, 200);
    }

    public function getFontTypeFile($fileName, GetFontTypeFileRequest $request) {
        /*
            Files that start with the word Default are 
            default files stored in the public folder.
        */
        
        if (mb_strpos($fileName, 'Default') === 0) {
            $imagePath = Storage::disk('default-files')->path('/fonts/' . $fileName);
        } else {
            $imagePath = Storage::path('/fonts/' . $fileName);
        }
        
        return response()->file($imagePath);
    }

    public function getFontTypesForLanguage($language, GetFontTypesForLanguageRequest $request) {
        try {
            $fonts = $this->fontTypeService->getFontTypesForLanguage(ucfirst($language));
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($fonts, 200);
    }

    public function uploadFontType(UploadFontTypeRequest $request) {
        $fontFile = $request->file('fontFile');
        $fontName = $request->post('name');
        $fontLanguages = $request->post('languages');

        try {
            $this->fontTypeService->uploadFontType($fontFile, $fontName, $fontLanguages);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Font type file has been uploaded successfully.', 200);
    }

    public function updateFontType(UpdateFontTypeRequest $request) {
        $fontId = $request->post('id');
        $fontName = $request->post('name');
        $fontLanguages = $request->post('languages');

        try {
            $this->fontTypeService->updateFontType($fontId, $fontName, $fontLanguages);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Font type file has been updated successfully.', 200);
    }

    public function deleteFontType(DeleteFontTypeRequest $request) {
        $fontId = $request->post('id');

        try {
            $this->fontTypeService->deleteFontType($fontId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Font type file has been deleted successfully.', 200);
    }
}
