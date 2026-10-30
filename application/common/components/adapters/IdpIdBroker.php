<?php

namespace Sil\Idp\IdSync\common\components\adapters;

use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\Idp\IdBroker\Client\ServiceException;
use Sil\Idp\IdSync\common\components\exceptions\MissingEmailException;
use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\models\User;

class IdpIdBroker extends IdBrokerBase
{
    /** @var IdBrokerClient */
    protected $client;

    public const DEFAULT_TIMEOUT = 10;
    public const DEFAULT_LIST_USERS_TIMEOUT = 30;
    public const DEFAULT_MAX_RETRIES = 2;

    /** @var int Timeout in seconds (per call to ID Broker API). */
    public $timeout = self::DEFAULT_TIMEOUT;

    /** @var int Timeout in seconds for the (potentially large) list of all users. */
    public $listUsersTimeout = self::DEFAULT_LIST_USERS_TIMEOUT;

    /** @var int How many times to retry a call that timed out or got a 502/503/504. */
    public $maxRetries = self::DEFAULT_MAX_RETRIES;

    public function init()
    {
        if (empty($this->accessToken)) {
            throw new InvalidArgumentException('An access token is required.');
        }
        if (empty($this->baseUrl)) {
            throw new InvalidArgumentException('A base URL is required.');
        }

        // An empty env var arrives as null, which would otherwise become 0 (no timeout).
        $this->timeout = self::defaultIfEmpty($this->timeout, self::DEFAULT_TIMEOUT);
        $this->listUsersTimeout = self::defaultIfEmpty($this->listUsersTimeout, self::DEFAULT_LIST_USERS_TIMEOUT);
        $this->maxRetries = self::defaultIfEmpty($this->maxRetries, self::DEFAULT_MAX_RETRIES);

        $this->client = new IdBrokerClient($this->baseUrl, $this->accessToken, [
            IdBrokerClient::ASSERT_VALID_BROKER_IP_CONFIG => $this->assertValidIp,
            IdBrokerClient::TRUSTED_IPS_CONFIG => $this->trustedIpRanges,
            'http_client_options' => [
                'timeout' => $this->timeout,
                'handler' => $this->getHandlerStack(),
            ],
        ]);
        parent::init();
    }

    private static function defaultIfEmpty($value, int $default): int
    {
        return ($value === null || $value === '') ? $default : (int)$value;
    }

    protected function getHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(
            [$this, 'shouldRetry'],
            [$this, 'getRetryDelay']
        ), 'retry');
        return $stack;
    }

    /**
     * Decide whether to retry a request. Only non-POST requests are retried, so
     * that a create that actually succeeded on the broker isn't sent again.
     */
    public function shouldRetry(
        int $retries,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        $exception = null
    ): bool {
        if ($retries >= $this->maxRetries || $request->getMethod() === 'POST') {
            return false;
        }
        if ($exception instanceof ConnectException) {
            $reason = $exception->getMessage();
        } elseif ($response !== null && in_array($response->getStatusCode(), [502, 503, 504], true)) {
            $reason = 'HTTP status ' . $response->getStatusCode();
        } else {
            return false;
        }
        \Yii::warning(sprintf(
            'Retrying ID Broker request %s %s (retry %d of %d) after: %s',
            $request->getMethod(),
            $request->getUri()->getPath(),
            $retries + 1,
            $this->maxRetries,
            $reason
        ));
        return true;
    }

    /**
     * @return int The number of milliseconds to wait before the given retry (1s, 2s, 4s, ...).
     */
    public function getRetryDelay(int $retries): int
    {
        return 1000 * (2 ** ($retries - 1));
    }

    /**
     * Create a user with the given information.
     *
     * @param array $config An array key/value pairs of attributes for the new
     *     user.
     * @return User User information.
     * @throws Exception
     */
    public function createUser(array $config = [])
    {
        $emailIsPresent = ! empty($config[User::EMAIL]);
        $personalEmailIsPresent = ! empty($config[User::PERSONAL_EMAIL]);
        $allowEmptyEmail = \Yii::$app->params['allowEmptyEmail'] ?? false;

        if ($emailIsPresent || ($allowEmptyEmail && $personalEmailIsPresent)) {
            return new User($this->getClient()->createUser($config));
        } else {
            throw new MissingEmailException(
                'An email address is required.',
                1494876311
            );
        }
    }

    /**
     * Deactivate a user.
     *
     * @param string $employeeId The Employee ID of the user to deactivate.
     * @throws Exception
     */
    public function deactivateUser(string $employeeId)
    {
        $this->getClient()->deactivateUser($employeeId);
    }

    /**
     * @return IdBrokerClient
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * Get information about the specified user.
     *
     * @param string $employeeId The Employee ID of the desired user.
     * @return User|null An array of information about the specified user, or
     *     null if no such user was found.
     * @throws Exception
     */
    public function getUser(string $employeeId)
    {
        $userInfo = $this->getClient()->getUser($employeeId);
        if ($userInfo === null) {
            return null;
        }
        return new User($userInfo);
    }

    public function getSiteStatus(): string
    {
        return $this->getClient()->getSiteStatus();
    }

    /**
     * Get a list of all users.
     *
     * @param array|null $fields (Optional:) The list of fields desired about
     *     each user in the result.
     * @return User[] A list of Users.
     * @throws Exception
     */
    public function listUsers($fields = null): array
    {
        try {
            $result = $this->getClient()->listUsers($fields, [
                '@http' => ['timeout' => $this->listUsersTimeout],
            ]);
        } catch (ServiceException $e) {
            $message = 'ID Broker returned non-OK status ' . $e->httpStatusCode;
            throw new Exception($message, $e->getCode(), $e);
        } catch (Exception $e) {
            $message = 'error getting users list from ID Broker: ' . $e->getMessage();
            throw new Exception($message, $e->getCode(), $e);
        }

        return self::getAsUsers($result);
    }

    /**
     * Update the specified user with the given information.
     *
     * @param array $config An array key/value pairs of attributes for the user.
     *     Must include at least an 'employee_id' entry.
     * @return User Information about the updated user.
     * @throws Exception
     */
    public function updateUser(array $config = [])
    {
        return new User($this->getClient()->updateUser($config));
    }
}
