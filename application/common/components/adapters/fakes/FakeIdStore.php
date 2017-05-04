<?php
namespace Sil\Idp\IdSync\common\components\adapters\fakes;

use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\components\adapters\InsiteIdStore;

class FakeIdStore extends IdStoreBase
{
    private $activeUsers;
    private $userChanges = [];
    
    public function __construct(
        array $activeUsers = [],
        array $userChanges = [],
        array $config = []
    ) {
        $this->activeUsers = $activeUsers;
        $this->userChanges = $userChanges;
        parent::__construct($config);
    }
    
    public function getActiveUser(string $employeeId)
    {
        $idStoreUser = $this->activeUsers[$employeeId] ?? null;
        if ($idStoreUser !== null) {
            return self::getAsUser($idStoreUser);
        }
        return null;
    }

    public function getUsersChangedSince(int $unixTimestamp)
    {
        $changesToReport = [];
        foreach ($this->userChanges as $userChange) {
            if ($userChange['changedat'] >= $unixTimestamp) {
                $changesToReport[] = [
                    'employeenumber' => $userChange['employeenumber'],
                ];
            }
        }
        return self::getAsUsers($changesToReport);
    }

    public function getAllActiveUsers()
    {
        return self::getAsUsers($this->activeUsers);
    }

    public static function getIdBrokerFieldNames()
    {
        // For simplicity's sake, just use the field names from Insite.
        return InsiteIdStore::getIdBrokerFieldNames();
    }
}
