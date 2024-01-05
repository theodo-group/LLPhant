<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Chat\Message;
use LLPhant\Experimental\Agent\AutoPHP;
use LLPhant\Experimental\Agent\Web\WebOutputUtils;
use LLPhant\Tool\SerpApiSearch;
use LLPhant\Tool\WebPageTextGetter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Chat extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): StreamedResponse
    {
        $id = uniqid();
        $webOutput = new WebOutputUtils($id);

        $webPageTextGetter = new WebPageTextGetter(true, $webOutput);
        $functionWebPageCrawler = FunctionBuilder::buildFunctionInfo($webPageTextGetter, 'getWebPageText');
        $searchApi = new SerpApiSearch(null, false, $webOutput);
        $functionSearch = FunctionBuilder::buildFunctionInfo($searchApi, 'googleSearch');

        /** @var Message[] $messages */
        $bodyContent = $request->getContent();

        $data = json_decode($bodyContent);

        $objective = 'exactly how many stars LLPhant have on github right now?';
        if (isset($data->objective)) {
            $objective = $data->objective;
        }

        @ob_end_clean();
        $response = new StreamedResponse();
        $autoPHP = new AutoPHP($objective, [$functionSearch, $functionWebPageCrawler], false, $webOutput);
        $response->setCallback(function () use ($autoPHP) {
            // Start the computation in $autoPHP
            $result = $autoPHP->run(5);
            echo json_encode(['result' => $result]);
        });
        echo json_encode(['id' => $id]);
        flush();

        return $response->send();
    }
}
