<?php

namespace Sil\Idp\IdSync\common\interfaces;

use Exception;
use Sil\Idp\IdSync\common\models\User;

interface IdBrokerInterface
{
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
     * Update the specified user with the given information.
     *
     * @param array $config An array key/value pairs of attributes for the user.
     *     Must include at least an 'employee_id' entry.
     * @return User Information about the updated user.
     * @throws Exception
     */
    public function updateUser(array $config = []);
}
