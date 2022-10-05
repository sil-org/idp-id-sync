<?php

namespace Sil\Idp\IdSync\common\interfaces;

use Exception;
use Sil\Idp\IdSync\common\models\User;

interface IdBrokerInterface
{
    /**
     * Activate a user.
     *
     * @param string $employeeId The Employee ID of the user to activate.
     * @throws Exception
     */
    public function activateUser(string $employeeId);

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
    public function authenticate(string $username, string $password);

    /**
     * Create a user with the given information.
     *
     * @param array $config An array key/value pairs of attributes for the new
     *     user.
     * @return User User information.
     * @throws Exception
     */
    public function createUser(array $config = []);

    /**
     * Deactivate a user.
     *
     * @param string $employeeId The Employee ID of the user to deactivate.
     * @throws Exception
     */
    public function deactivateUser(string $employeeId);

    /**
     * Ping the /site/status URL.
     *
     * @return string "OK".
     * @throws Exception
     */
    public function getSiteStatus();

    /**
     * Get information about the specified user.
     *
     * @param string $employeeId The Employee ID of the desired user.
     * @return User|null An array of information about the specified user, or
     *     null if no such user was found.
     * @throws Exception
     */
    public function getUser(string $employeeId);

    /**
     * Get a list of all users.
     *
     * @param array|null $fields (Optional:) The list of fields desired about
     *     each user in the result.
     * @return User[] A list of Users.
     */
    public function listUsers($fields = null);

    /**
     * Set the password for the specified user.
     *
     * @param string $employeeId The Employee ID of the user whose password we
     *     are trying to set.
     * @param string $password The desired (new) password, in plaintext.
     * @throws Exception
     */
    public function setPassword(string $employeeId, string $password);

    /**
     * Update the specified user with the given information.
     *
     * @param array $config An array key/value pairs of attributes for the user.
     *     Must include at least an 'employee_id' entry.
     * @return User Information about the updated user.
     * @throws Exception
     */
    public function updateUser(array $config = []);
}
