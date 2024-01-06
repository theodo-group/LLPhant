<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LLPhant\Experimental\Agent\Render\WebOutputUtils;

class Output extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $id = $request->query('id', 'default');
        $webOutput = new WebOutputUtils($id);
        $data = $webOutput->readMessagesFromFile();

        return response()->json($data);
    }
}
