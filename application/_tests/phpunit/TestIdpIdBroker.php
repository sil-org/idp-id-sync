<?php

namespace Sil\Idp\IdSync\_tests\phpunit;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Sil\Idp\IdSync\common\components\adapters\IdpIdBroker;

/**
 * IdpIdBroker that sends requests to a Guzzle MockHandler and doesn't wait
 * between retries.
 */
class TestIdpIdBroker extends IdpIdBroker
{
    /** @var MockHandler */
    public $mockHandler;

    protected function getHandlerStack(): HandlerStack
    {
        $stack = parent::getHandlerStack();
        $stack->setHandler($this->mockHandler);
        return $stack;
    }

    public function getRetryDelay(int $retries): int
    {
        return 0;
    }
}
