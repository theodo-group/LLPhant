<?php

namespace LLPhant\Tool;

use Exception;
use LLPhant\Experimental\Agent\Render\CLIOutputUtils;
use LLPhant\Experimental\Agent\Render\OutputAgentInterface;

class WebPageTextGetter extends ToolBase
{
    /**
     * @throws Exception
     */
    public function __construct(bool $verbose = false, public OutputAgentInterface $outputAgent = new CLIOutputUtils())
    {
        parent::__construct($verbose);
    }

    /**
     * Get the content of a web page by its URL.
     *
     * @throws \Exception
     */
    public function getWebPageText(string $url): string
    {
        $this->outputAgent->renderTitleAndMessageOrange('🔧 retrieving web page content', $url, true);

        try {
            $html = file_get_contents($url);
            if ($html === false) {
                throw new \Exception('Unable to retrieve web page content');
            }
            $text = (string) preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
            $text = (string) preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $text);
            $text = strip_tags($text);
            $text = html_entity_decode($text);
            $text = str_replace("\n", '.', $text);
            $text = str_replace("\t", '.', $text);
            $text = str_replace("\r", '.', $text);
            $text = (string) preg_replace('/( )+/', ' ', $text);

            return (string) preg_replace('/((\.)|( \.))+/', '.', $text);
        } catch (Exception) {
            return 'We couldn\'t retrieve the web page content from the url provided '.$url;
        }
    }
}
