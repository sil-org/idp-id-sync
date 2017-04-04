<?php
namespace Sil\Idp\IdSync\common\components\adapters\fakes;

use yii\base\NotSupportedException;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\components\adapters\InsiteIdStore;

class FakeIdStore extends IdStoreBase
{
    private $activeUsers;
    
    public function __construct(array $activeUsers = [], array $config = [])
    {
        $this->activeUsers = $activeUsers;
        parent::__construct($config);
    }
    
    public function getActiveUser(string $employeeNumber)
    {
        $idStoreUser = $this->activeUsers[$employeeNumber] ?? null;
        if ($idStoreUser !== null) {
            return $this->translateToIdBrokerFieldNames($idStoreUser);
        }
        return null;
    }

    public function getActiveUsersChangedSince(int $unixTimestamp)
    {
        throw new NotSupportedException();
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
