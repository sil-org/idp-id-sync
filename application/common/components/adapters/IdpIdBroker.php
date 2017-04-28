<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use InvalidArgumentException;
use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\Idp\IdSync\common\components\IdBrokerBase;

class IdpIdBroker extends IdBrokerBase
{
    protected $client = null;
    
    public function init()
    {
        if (empty($this->accessToken)) {
            throw new InvalidArgumentException('An access token is required.');
        }
        if (empty($this->baseUrl)) {
            throw new InvalidArgumentException('A base URL is required.');
        }
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
     * @return array|null An array of user information (if valid), or null.
     * @throws Exception
     */
    public function authenticate(string $username, string $password)
    {
        return $this->getClient()->authenticate($username, $password);
    }
    
    /**
     * Create a user with the given information.
     *
     * @param array $config An array key/value pairs of attributes for the new
     *     user.
     * @return array An array of information about the new user.
     * @throws Exception
     */
    public function createUser(array $config = [])
    {
        return $this->getClient()->createUser($config);
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
        if ($this->client === null) {
            $this->client = new IdBrokerClient($this->baseUrl, $this->accessToken);
        }
        return $this->client;
    }
    
    /**
     * Get information about the specified user.
     *
     * @param string $employeeId The Employee ID of the desired user.
     * @return array|null An array of information about the specified user, or
     *     null if no such user was found.
     * @throws Exception
     */
    public function getUser(string $employeeId)
    {
        return $this->getClient()->getUser($employeeId);
    }
    
    /**
     * Get a list of all users.
     *
     * @param array|null $fields (Optional:) The list of fields desired about
     *     each user in the result.
     * @return array An array with a sub-array about each user.
     */
    public function listUsers($fields = null)
    {
        return $this->getClient()->listUsers($fields);
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
     * @return array Information about the updated user.
     * @throws Exception
     */
    public function updateUser(array $config = [])
    {
        return $this->getClient()->updateUser($config);
    }
}
