<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

use App\Models\User;
use App\Services\GoalService;
use App\Services\StatisticsService;

// request classes
use App\Http\Requests\Home\GetConfigRequest;

class HomeController extends Controller {

    private $statisticsService;
    private $goalService;

    public function __construct(StatisticsService $statisticsService, GoalService $goalService) {
        $this->middleware('auth');

        $this->statisticsService = $statisticsService;
        $this->goalService = $goalService;
    }

    public function index() {
        $selectedLanguage = Auth::user()->selected_language;
        $userCount = User::count();
        $userName = Auth::user()->name;
        $isAdmin = Auth::user()->is_admin;
        $theme = $_COOKIE['theme'] ?? 'light';

        return view('home', [
            'language' => $selectedLanguage,
            'userCount' => $userCount,
            'userName' => $userName,
            'isAdmin' => $isAdmin,
            'theme' => $theme
        ]);
    }

    public function getStatistics() {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;

        try {
            $statistics = $this->statisticsService->getStatistics($userId, $language);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($statistics, 200);
    }

    public function getConfig($configPath, GetConfigRequest $request) {
        if (!config()->has($configPath)) {
            abort(500, 'Requested config value does not exist.');
        }

        $config = config($configPath);
        return response()->json($config, 200);
    }

    public function getUserManualTree() {
        $manualTree = [];

        $path = public_path('./../manual/');
        $files = scandir($path);

        $index = 0;
        foreach ($files as $file) {
            // skip
            if ($file === '.' || $file === '..') {
                continue;
            }

            // create page;
            $page = new \stdClass();
            $page->id = $index;
            $page->name = str_replace('.md', '', $file);
            $page->fileName = str_replace('.md', '', $file);
            $page->level = 0;
            $index ++;

            // get subpages
            $subPages = [];
            $handle = fopen('./../manual/' . $file, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    // if line starts with "# "
                    if (strpos($line, '# ') === 0) {
                        $subPageName = substr($line, 2);
                        $subPageName = str_replace("\r\n", '', $subPageName);
                        $subPageName = str_replace("\n", '', $subPageName);
                        $subPageName = str_replace("\n", '', $subPageName);

                        $subPage = new \stdClass();
                        $subPage->id = $index;
                        $subPage->name = $subPageName;
                        $subPage->fileName = str_replace('.md', '', $file) . '#' . $subPageName;
                        $subPage->level = 1;
                        $subPages[] = $subPage;
                        $index ++;
                    }
                }

                fclose($handle);
            }

            if (count($subPages)) {
                $page->children = $subPages;
            }

            $manualTree[] = $page;
        }

        return response()->json($manualTree, 200);
    }

    public function getUserManualFile($fileName) {
        return response()->file('./../manual/' . $fileName . '.md');
    }
}
