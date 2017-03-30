<?php
namespace Sil\Idp\IdSync\common\interfaces;

interface IdStoreInterface
{
    /**
     * Get the specified user's information (using ID Broker field names). Note
     * that inactive users will be treated as non-existent users.
     *
     * @param string $employeeNumber The employee number/ID.
     */
    public function getActiveUser(string $employeeNumber);
    
    /**
     * Get information about each of the (active) users (using ID Broker field
     * names).
     */
    public function getAllActiveUsers();
    
    /**
     * Get a list of users who have had qualifying changes (name, email, locked,
     * activated, added) since the given Unix timestamp. As with all the other
     * functions, the results need to provide user info using ID Broker field
     * names (not ID Store field names).
     * 
     * @param int $unixTimestamp The date/time, as a Unix timestamp.
     */
    public function getActiveUsersChangedSince(int $unixTimestamp);
}
