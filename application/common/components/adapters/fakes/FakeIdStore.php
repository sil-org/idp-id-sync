<?php
namespace Sil\Idp\IdSync\common\components\adapters\fakes;

use yii\base\NotSupportedException;
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
            return $this->translateToIdBrokerFieldNames($idStoreUser);
        }
        return null;
    }

    public function getUsersChangedSince(int $unixTimestamp)
    {
        $changesToReport = [];
        foreach ($this->userChanges as $userChange) {
            if ($userChange['changedAt'] >= $unixTimestamp) {
                $changesToReport[] = [
                    'employeeNumber' => $userChange['employeeNumber'],
                ];
            }
        }
        return $changesToReport;
    }

    public function getAllActiveUsers()
    {
        return array_map(function($entry) {
            return $this->translateToIdBrokerFieldNames($entry);
        }, $this->activeUsers);
    }

    public static function getIdBrokerFieldNames()
    {
        // For simplicity's sake, just use the field names from Insite.
        return InsiteIdStore::getIdBrokerFieldNames();
    }
}
