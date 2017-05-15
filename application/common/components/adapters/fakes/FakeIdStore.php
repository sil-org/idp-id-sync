<?php
namespace Sil\Idp\IdSync\common\components\adapters\fakes;

use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\components\adapters\InsiteIdStore;
use yii\helpers\ArrayHelper;

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
    
    /**
     * WARNING: This function only exists on the FAKE ID Store, and should only
     * be used for setting up tests.
     *
     * @param string $employeeId
     * @param array $changes
     */
    public function changeFakeRecord(string $employeeId, array $changes)
    {
        $record = $this->activeUsers[$employeeId];
        $this->activeUsers[$employeeId] = ArrayHelper::merge($record, $changes);
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

    public function getIdStoreName(): string
    {
        return 'the fake ID Store';
    }
}
