<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FontTypeService;

// request classes
use App\Http\Requests\FontTypes\UploadFontTypeRequest;

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
            $this->fontTypeService->uploadfontType($fontFile, $fontName, $fontLanguages);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Font type file has been uploaded successfully.', 200);
    }
}
