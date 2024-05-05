<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FontTypeService;

// request classes
use App\Http\Requests\FontTypes\UploadFontTypeRequest;
use App\Http\Requests\FontTypes\UpdateFontTypeRequest;

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
}
