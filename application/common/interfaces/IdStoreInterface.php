<?php
namespace Sil\Idp\IdSync\common\interfaces;

interface IdStoreInterface
{
    /**
     * Get the specified user's information (using ID Broker field names). Note
     * that inactive users will be treated as non-existent users.
     *
     * @param string $employeeId The Employee ID.
     * @return array|null Information about the specified user, or null if no
     *     such active user was found.
     */
    public function getActiveUser(string $employeeId);
    
    /**
     * Get information about each of the (active) users (using ID Broker field
     * names).
     *
     * @return array A list of user-information arrays.
     */
    public function getAllActiveUsers();
    
    /**
     * Get a list of users who have had qualifying changes (name, email, locked,
     * activated, added) since the given Unix timestamp. As with all the other
     * functions, the results need to provide user info using ID Broker field
     * names (not ID Store field names).
     * 
     * @param int $unixTimestamp The date/time, as a Unix timestamp.
     * @return array A list of user-information arrays.
     */
    public function getActiveUsersChangedSince(int $unixTimestamp);
}
