<?php

namespace Sil\Idp\IdSync\_tests\phpunit;

use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sil\Idp\IdSync\common\components\clients\GoogleSheetsClient;

class GoogleSheetsClientTest extends TestCase
{
    /** Google sometimes sends an HTML page instead of JSON for a 5xx error. */
    private const HTML_ERROR_PAGE = '<!DOCTYPE html><html><title>Error 502 (Server Error)!!1</title></html>';

    /** @var MockHandler */
    private $mockHandler;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
    }

    public function testGetAllUsersInfoRetriesAfterBadGateway()
    {
        $client = $this->getClient([
            $this->htmlErrorResponse(502),
            new Response(504),
            $this->usersResponse(),
        ]);

        $users = $client->getAllUsersInfo();

        $this->assertCount(1, $users);
        $this->assertSame('123', $users[0]['employee_id']);
        $this->assertSame(0, $this->mockHandler->count(), 'Not all queued responses were used');
    }

    public function testGiveUpAfterMaxRetries()
    {
        $client = $this->getClient(array_merge(
            array_fill(0, GoogleSheetsClient::MAX_RETRIES + 1, $this->htmlErrorResponse(502)),
            [$this->usersResponse()]
        ));

        try {
            $client->getAllUsersInfo();
            $this->fail('Expected an exception');
        } catch (GoogleServiceException $e) {
            $this->assertSame(502, $e->getCode());
        }
        $this->assertSame(1, $this->mockHandler->count(), 'Expected exactly MAX_RETRIES + 1 attempts');
    }

    public function testClientErrorIsNotRetried()
    {
        $json = '{"error": {"code": 400, "message": "Bad range", "errors": [{"reason": "badRequest"}]}}';
        $client = $this->getClient([
            new Response(400, ['Content-Type' => 'application/json'], $json),
            $this->usersResponse(),
        ]);

        try {
            $client->getAllUsersInfo();
            $this->fail('Expected an exception');
        } catch (GoogleServiceException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame('badRequest', $e->getErrors()[0]['reason']);
        }
        $this->assertSame(1, $this->mockHandler->count(), 'Expected exactly 1 attempt');
    }

    private function getClient(array $queue): GoogleSheetsClient
    {
        $this->mockHandler = new MockHandler($queue);
        return new TestGoogleSheetsClient([
            'applicationName' => 'id-sync test',
            'jsonAuthString' => '{}',
            'spreadsheetId' => 'abc123',
            'mockHandler' => $this->mockHandler,
        ]);
    }

    private function htmlErrorResponse(int $status): Response
    {
        return new Response($status, ['Content-Type' => 'text/html; charset=UTF-8'], self::HTML_ERROR_PAGE);
    }

    private function usersResponse(): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'range' => 'Users!A2:O101',
            'majorDimension' => 'ROWS',
            'values' => [['123', 'Test', 'User']],
        ]));
    }
}
