<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Context\Context;
use GuzzleHttp\Client;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Sil\PhpEnv\Env;


/**
 * Defines application features from the specific context.
 */
class WebhookContext implements Context
{
    private $urlPath;
    
    /** @var ResponseInterface */
    private $response;
    
    /**
     * @Given the notification URL path will be :urlPath
     */
    public function aNotificationToContains($urlPath)
    {
        $this->urlPath = $urlPath;
    }

    /**
     * @When ID Sync receives the notification
     */
    public function idSyncReceivesTheNotification()
    {
        $client = new Client([
            'base_uri' => 'http://app',
            'http_errors' => false, // Don't throw exceptions on 4xx/5xx.
            'headers' => [
                'Authorization' => 'Bearer abc123',
            ],
        ]);
        $this->response = $client->get($this->urlPath);
    }

    /**
     * @Then it should return a status code of :responseCode
     */
    public function itShouldReturnAStatusCodeOf($responseCode)
    {
        Assert::assertSame((int)$responseCode, $this->response->getStatusCode());
    }
}
