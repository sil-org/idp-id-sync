<?php

namespace Sil\Idp\IdSync\common\components\adapters;

use Exception;
use InvalidArgumentException;
use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\Idp\IdBroker\Client\ServiceException;
use Sil\Idp\IdSync\common\components\exceptions\MissingEmailException;
use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\models\User;

class IdpIdBroker extends IdBrokerBase
{
    /** @var IdBrokerClient */
    protected $client;

    public function init()
    {
        if (empty($this->accessToken)) {
            throw new InvalidArgumentException('An access token is required.');
        }
        if (empty($this->baseUrl)) {
            throw new InvalidArgumentException('A base URL is required.');
        }
        $this->client = new IdBrokerClient($this->baseUrl, $this->accessToken, [
            IdBrokerClient::ASSERT_VALID_BROKER_IP_CONFIG => $this->assertValidIp,
            IdBrokerClient::TRUSTED_IPS_CONFIG => $this->trustedIpRanges,
        ]);
        parent::init();
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
            $result = $this->getClient()->listUsers($fields);
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
