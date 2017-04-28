<?php
namespace Sil\Idp\IdSync\common\components\adapters\fakes;

use Sil\Idp\IdSync\common\components\IdBrokerBase;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;

class FakeIdBroker extends IdBrokerBase
{
    private $usersByEmployeeId;
    
    public function __construct(array $usersByEmployeeId = [], array $config = [])
    {
        $this->usersByEmployeeId = $usersByEmployeeId;
        parent::__construct($config);
    }
    
    public function activateUser(string $employeeId)
    {
        $this->usersByEmployeeId[$employeeId]['active'] = 'yes';
    }

    public function authenticate(string $username, string $password)
    {
        throw new NotSupportedException();
    }

    public function createUser(array $config = [])
    {
        $this->usersByEmployeeId[$config['employee_id']] = ArrayHelper::merge(
            ['active' => 'yes'], // 'active' should default to 'yes'
            $config
        );
        return $this->usersByEmployeeId[$config['employee_id']];
    }

    public function deactivateUser(string $employeeId)
    {
        $this->usersByEmployeeId[$employeeId]['active'] = 'no';
    }

    public function getUser(string $employeeId)
    {
        return $this->usersByEmployeeId[$employeeId] ?? null;
    }

    public function listUsers($fields = null)
    {
        $results = [];
        foreach ($this->usersByEmployeeId as $user) {
            if ($fields === null) {
                $tempUser = $user;
            } else {
                $tempUser = [];
                foreach ($fields as $field) {
                    $tempUser[$field] = $user[$field] ?? null;
                }
            }
            $results[] = $tempUser;
        }
        return $results;
    }

    public function setPassword(string $employeeId, string $password)
    {
        throw new NotSupportedException();
    }

    public function updateUser(array $config = [])
    {
        $employeeId = $config['employee_id'];
        $user = $this->usersByEmployeeId[$employeeId];
        foreach ($config as $attribute => $newValue) {
            $user[$attribute] = $newValue;
        }
        $this->usersByEmployeeId[$employeeId] = $user;
        return $user;
    }
}
