<?php
namespace Sil\Idp\IdSync\common\components\adapters\fakes;

use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\components\adapters\InsiteIdStore;
use Sil\Idp\IdSync\common\models\User;
use yii\helpers\ArrayHelper;

class FakeIdStore extends IdStoreBase
{
    private $activeUsers = [];
    private $userChanges = [];
    
    /**
     * @param array $activeUsersSparseInfo - An array (indexed by employee id)
     *     of info about ACTIVE users (which may each include only a subset of
     *     possible ID Store fields).
     * @param array[] $userChanges Information about which users were changed
     *     when. Each entry is an array with a 'changedat' and an 'employeeid'.
     * @param array $config
     */
    public function __construct(
        array $activeUsersSparseInfo = [],
        array $userChanges = [],
        array $config = []
    ) {
        foreach ($activeUsersSparseInfo as $employeeId => $sparseUserInfo) {
            $this->addUserFromSparseInfo($employeeId, $sparseUserInfo);
        }
        $this->userChanges = $userChanges;
        parent::__construct($config);
    }
    
    /**
     * Take the (potentially incomplete) user info and add null values for all
     * missing fields, then add the result to our list of active users in this
     * (fake) ID Store.
     *
     * @param string $employeeId
     * @param array $sparseUserInfo
     */
    private function addUserFromSparseInfo(string $employeeId, array $sparseUserInfo)
    {
        $userInfo = [];
        foreach (array_keys(self::getIdBrokerFieldNames()) as $idStoreFieldName) {
            $userInfo[$idStoreFieldName] = $sparseUserInfo[$idStoreFieldName] ?? null;
        }
        $this->activeUsers[$employeeId] = $userInfo;
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
