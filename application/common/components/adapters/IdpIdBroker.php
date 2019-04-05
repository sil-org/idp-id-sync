<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use Exception;
use InvalidArgumentException;
use Sil\Idp\IdBroker\Client\IdBrokerClient;
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
     * Activate a user.
     *
     * @param string $employeeId The Employee ID of the user to activate.
     * @throws Exception
     */
    public function activateUser(string $employeeId)
    {
        $this->getClient()->updateUser([
            'employee_id' => $employeeId,
            'active' => 'yes',
        ]);
    }
    
    /**
     * Attempt to authenticate using the given credentials, getting back
     * information about the authenticated user (if the credentials were
     * acceptable) or null (if unacceptable).
     *
     * @param string $username The username.
     * @param string $password The password (in plaintext).
     * @return User|null User information (if valid), or null.
     * @throws Exception
     */
    public function authenticate(string $username, string $password)
    {
        $authenticatedUserInfo = $this->getClient()->authenticate(
            $username,
            $password
        );
        if ($authenticatedUserInfo === null) {
            return null;
        }
        return new User($authenticatedUserInfo);
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
        $emailIsMissing = empty($config[User::EMAIL]);
        $personalEmailIsPresent = ! empty($config[User::PERSONAL_EMAIL]);
        $allowEmptyEmail = \Yii::$app->params['allowEmptyEmail'];

        if ($allowEmptyEmail && $emailIsMissing && $personalEmailIsPresent) {
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
     */
    public function listUsers($fields = null)
    {
        return self::getAsUsers($this->getClient()->listUsers($fields));
    }
    
    /**
     * Set the password for the specified user.
     *
     * @param string $employeeId The Employee ID of the user whose password we
     *     are trying to set.
     * @param string $password The desired (new) password, in plaintext.
     * @throws Exception
     */
    public function setPassword(string $employeeId, string $password)
    {
        $this->getClient()->setPassword($employeeId, $password);
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
