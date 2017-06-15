<?php
namespace Sil\Idp\IdSync\common\interfaces;

use Sil\Idp\IdSync\common\models\User;

interface IdStoreInterface
{
    /**
     * Get the specified user's information. Note that inactive users will be
     * treated as non-existent users.
     *
     * @param string $employeeId The Employee ID.
     * @return User|null Information about the specified user, or null if no
     *     such active user was found.
     */
    public function getActiveUser(string $employeeId);
    
    /**
     * Get information about each of the (active) users.
     *
     * @return User[] A list of Users.
     */
    public function getAllActiveUsers();
    
    /**
     * Get a user-friendly name for this ID Store.
     *
     * @return string The name of the ID Store.
     */
    public function getIdStoreName();
    
    /**
     * Get a list of users who have had qualifying changes (name, email, locked,
     * activated, added) since the given Unix timestamp.
     * 
     * @param int $unixTimestamp The date/time, as a Unix timestamp.
     * @return User[] A list of Users.
     */
    public function getUsersChangedSince(int $unixTimestamp);
}
