<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use Exception;
use Sil\Idp\IdSync\common\components\IdStoreBase;

class InsiteIdStore extends IdStoreBase
{
    public static function getIdBrokerFieldNames()
    {
        return [
            'employeeNumber' => self::ID_BROKER_EMPLOYEE_ID,
            'firstName' => self::ID_BROKER_FIRST_NAME,
            'lastName' => self::ID_BROKER_LAST_NAME,
            'displayName' => self::ID_BROKER_DISPLAY_NAME,
            'email' => self::ID_BROKER_EMAIL,
            'username' => self::ID_BROKER_USERNAME,
            'locked' => self::ID_BROKER_LOCKED,
            // No 'active' needed, since all ID Store records returned are active.
        ];
    }
    
    public function getActiveUser(string $employeeId)
    {
        throw new Exception('Not yet implemented');
    }

    public function getActiveUsersChangedSince(int $unixTimestamp): array
    {
        throw new Exception('Not yet implemented');
    }

    public function getAllActiveUsers(): array
    {
        throw new Exception('Not yet implemented');
    }
}
