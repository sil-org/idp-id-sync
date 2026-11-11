<?php

namespace Sil\Idp\IdSync\_tests\phpunit;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Sil\Idp\IdSync\common\components\clients\GoogleSheetsClient;

/**
 * GoogleSheetsClient that sends requests to a Guzzle MockHandler, records
 * them, and doesn't wait between retries.
 */
class TestGoogleSheetsClient extends GoogleSheetsClient
{
    /** @var MockHandler */
    public $mockHandler;

    /** @var array Every request sent (including retries), from Guzzle's history middleware. */
    public $history = [];

    protected function getHandlerStack(): HandlerStack
    {
        $stack = parent::getHandlerStack();
        $stack->setHandler($this->mockHandler);
        $stack->push(Middleware::history($this->history));
        return $stack;
    }

    public function getRetryDelay(int $retries): int
    {
        return 0;
    }
}
