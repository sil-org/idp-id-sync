<?php

namespace Sil\Idp\IdSync\_tests\phpunit;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use Sil\Idp\IdSync\common\components\clients\GoogleSheetsClient;

/**
 * GoogleSheetsClient that sends requests to a Guzzle MockHandler, skips auth,
 * and doesn't wait between retries.
 */
class TestGoogleSheetsClient extends GoogleSheetsClient
{
    /** @var MockHandler */
    public $mockHandler;

    protected function createGoogleClient(): \Google_Client
    {
        $config = $this->getGoogleClientConfig();
        $config['retry']['initial_delay'] = 0;
        $googleClient = new \Google_Client($config);
        $googleClient->setHttpClient(new GuzzleClient(['handler' => $this->mockHandler]));
        return $googleClient;
    }
}
