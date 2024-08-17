<?php

namespace West\UserApiKey\Service\Store;

use West\UserApiKey\Entity\UserStore;
use XF\Service\AbstractService;

class Checker extends AbstractService
{
    /** @var UserStore */
    protected $store;

    public function __construct(\XF\App $app, UserStore $store)
    {
        parent::__construct($app);

        $this->store = $store;
    }

    protected function getPageHtml(string &$errorCode = null): ?string
    {
        $response = $this->app->http()->reader()
            ->getUntrusted($this->store->store_url, [], null, [], $error);

        if ($error)
        {
            \XF::logError($error);

            $errorCode = 'connection';
            return null;
        }

        if ($response->getStatusCode() != 200)
        {
            $errorCode = 'status_code';
            return null;
        }

        return $response->getBody()->getContents();
    }

    public function check(string &$errorCode = null): string
    {
        $html = $this->getPageHtml($errorCode);
        if (!$html)
        {
            return 'error';
        }

        $options = $this->app->options();
        $checkUrl = $options->wuakCheckUrl ?: $options->boardUrl;
        $linkFound = false;

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $linkList = $dom->getElementsByTagName('a');
        foreach ($linkList as $link)
        {
            $href = $link->attributes->getNamedItem('href');
            if ($href && $href->value == $checkUrl)
            {
                $linkFound = true;
                break;
            }
        }

        return $linkFound ? 'valid' : 'missing_link';
    }
}