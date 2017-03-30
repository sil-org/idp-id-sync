<?php
namespace Sil\Idp\IdSync\tests\fakes;

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

    public function authenticate(array $config = [])
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

    public function deactivateUser(array $config = [])
    {
        $this->usersByEmployeeId[$config['employee_id']]['active'] = $config['active'];
    }

    public function getUser(array $config = [])
    {
        return $this->usersByEmployeeId[$config['employee_id']];
    }

    public function listUsers(array $config = [])
    {
        $results = [];
        foreach ($this->usersByEmployeeId as $user) {
            $results[] = [
                'employee_id' => $user['employee_id'],
                'active' => $user['active'] ?? 'yes',
            ];
        }
        return $results;
    }

    public function setPassword(array $config = [])
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
    }
}
