<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Experimental\Agent\AutoPHP;
use LLPhant\Experimental\Agent\Render\WebOutputUtils;
use LLPhant\Tool\SerpApiSearch;
use LLPhant\Tool\WebPageTextGetter;

class Chat extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $bodyContent = $request->getContent();
        $data = json_decode($bodyContent);
        var_dump($data);
        $objective = $data->objective;
        $id = $data->id;
        $webOutput = new WebOutputUtils($id);
        $webPageTextGetter = new WebPageTextGetter(true, $webOutput);
        $functionWebPageCrawler = FunctionBuilder::buildFunctionInfo($webPageTextGetter, 'getWebPageText');
        $searchApi = new SerpApiSearch(null, false, $webOutput);
        $functionSearch = FunctionBuilder::buildFunctionInfo($searchApi, 'googleSearch');

        $response = new Response();
        $response->send();

        $autoPHP = new AutoPHP($objective, [$functionSearch, $functionWebPageCrawler], false, $webOutput);
        $autoPHP->run(5);
    }
}
