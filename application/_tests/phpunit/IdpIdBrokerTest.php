<?php

namespace Sil\Idp\IdSync\_tests\phpunit;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sil\Idp\IdSync\common\components\adapters\IdpIdBroker;

class IdpIdBrokerTest extends TestCase
{
    /** @var MockHandler */
    private $mockHandler;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
    }

    public function testUpdateUserRetriesAfterTimeout()
    {
        $idBroker = $this->getIdBroker([
            $this->timeoutException('PUT'),
            $this->timeoutException('PUT'),
            $this->userResponse(),
        ]);

        $user = $idBroker->updateUser(['employee_id' => '123']);

        $this->assertSame('123', $user->getEmployeeId());
        $this->assertSame(0, $this->mockHandler->count(), 'Not all queued responses were used');
        $this->assertSame(10, $this->mockHandler->getLastOptions()['timeout']);
    }

    public function testUpdateUserGivesUpAfterMaxRetries()
    {
        $idBroker = $this->getIdBroker([
            $this->timeoutException('PUT'),
            $this->timeoutException('PUT'),
            $this->timeoutException('PUT'),
            $this->userResponse(),
        ]);

        $this->expectException(\Exception::class);
        try {
            $idBroker->updateUser(['employee_id' => '123']);
        } finally {
            $this->assertSame(1, $this->mockHandler->count(), 'Expected exactly 3 attempts');
        }
    }

    public function testUpdateUserRetriesAfterServiceUnavailable()
    {
        $idBroker = $this->getIdBroker([
            new Response(503),
            $this->userResponse(),
        ]);

        $user = $idBroker->updateUser(['employee_id' => '123']);

        $this->assertSame('123', $user->getEmployeeId());
        $this->assertSame(0, $this->mockHandler->count());
    }

    public function testCreateUserIsNotRetried()
    {
        $idBroker = $this->getIdBroker([
            $this->timeoutException('POST'),
            $this->userResponse(),
        ]);

        $this->expectException(\Exception::class);
        try {
            $idBroker->createUser([
                'employee_id' => '123',
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => 'test_user',
                'email' => 'test@example.com',
            ]);
        } finally {
            $this->assertSame(1, $this->mockHandler->count(), 'Expected exactly 1 attempt');
        }
    }

    public function testListUsersUsesLongerTimeout()
    {
        $idBroker = $this->getIdBroker([
            new Response(200, [], json_encode([['employee_id' => '123']])),
        ]);

        $users = $idBroker->listUsers(['employee_id']);

        $this->assertCount(1, $users);
        $this->assertSame(30, $this->mockHandler->getLastOptions()['timeout']);
    }

    public function testEmptyConfigValuesUseDefaults()
    {
        $idBroker = $this->getIdBroker([], [
            'timeout' => null,
            'listUsersTimeout' => '',
            'maxRetries' => null,
        ]);

        $this->assertSame(IdpIdBroker::DEFAULT_TIMEOUT, $idBroker->timeout);
        $this->assertSame(IdpIdBroker::DEFAULT_LIST_USERS_TIMEOUT, $idBroker->listUsersTimeout);
        $this->assertSame(IdpIdBroker::DEFAULT_MAX_RETRIES, $idBroker->maxRetries);
    }

    public function testZeroConfigValuesAreKept()
    {
        $idBroker = $this->getIdBroker([], [
            'timeout' => '0',
            'listUsersTimeout' => '0',
            'maxRetries' => '0',
        ]);

        $this->assertSame(0, $idBroker->timeout);
        $this->assertSame(0, $idBroker->listUsersTimeout);
        $this->assertSame(0, $idBroker->maxRetries);
    }

    private function getIdBroker(array $queue, array $config = []): IdpIdBroker
    {
        $this->mockHandler = new MockHandler($queue);
        return new TestIdpIdBroker(array_merge([
            'accessToken' => 'abc123',
            'assertValidIp' => false,
            'baseUrl' => 'http://broker.example.com',
            'mockHandler' => $this->mockHandler,
        ], $config));
    }

    private function timeoutException(string $method): ConnectException
    {
        return new ConnectException(
            'cURL error 28: Operation timed out',
            new Request($method, 'http://broker.example.com/user')
        );
    }

    private function userResponse(): Response
    {
        return new Response(200, [], json_encode(['employee_id' => '123']));
    }
}

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
