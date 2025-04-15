<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index()
    {

        $locale = app()->getLocale();
        $messagesPath = resource_path("lang/{$locale}.json");
        if (!file_exists($messagesPath)) {
            return response()->json([], 404);
        }
        $messages = json_decode(file_get_contents($messagesPath), true);
        return response()->json($messages);
    }
}
