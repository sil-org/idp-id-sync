<?php
namespace Sil\Idp\IdSync\tests\fakes;

use yii\base\NotSupportedException;
use Sil\Idp\IdSync\common\components\IdStoreBase;

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
}
