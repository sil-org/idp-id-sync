<?php

namespace Sil\Idp\IdSync\common\components\adapters\fakes;

use Sil\Idp\IdSync\common\components\exceptions\MissingEmailException;
use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\models\User;
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
        /*
         * NOTE: Only have the FakeIdBroker require a value for 'email' if the
         * given $config includes an 'email' key. This is to avoid having to
         * include a dummy email address in our tests where the email address
         * would be irrelevant.
         */
        if (array_key_exists(User::EMAIL, $config) && empty($config[User::EMAIL])) {
            throw new MissingEmailException(
                'An email address is required.',
                1494880621
            );
        }

        $this->usersByEmployeeId[$config['employee_id']] = ArrayHelper::merge(
            ['active' => 'yes'], // 'active' should default to 'yes'
            $config
        );
        return new User($this->usersByEmployeeId[$config['employee_id']]);
    }

    public function deactivateUser(string $employeeId)
    {
        $this->usersByEmployeeId[$employeeId]['active'] = 'no';
    }

    public function getUser(string $employeeId)
    {
        $userInfo = $this->usersByEmployeeId[$employeeId] ?? null;
        if ($userInfo === null) {
            return null;
        }
        return new User($userInfo);
    }

    public function getSiteStatus(): string
    {
        throw new NotSupportedException();
    }

    public function listUsers($fields = null)
    {
        $results = [];
        foreach ($this->usersByEmployeeId as $userInfo) {
            if ($fields === null) {
                $tempUserInfo = $userInfo;
            } else {
                $tempUserInfo = [];
                foreach ($fields as $field) {
                    $tempUserInfo[$field] = $userInfo[$field] ?? null;
                }
            }
            $results[] = new User($tempUserInfo);
        }
        return $results;
    }

    public function setPassword(string $employeeId, string $password)
    {
        throw new NotSupportedException();
    }

    public function updateUser(array $config = [])
    {
        if (array_key_exists('email', $config) && empty($config['email'])) {
            throw new \InvalidArgumentException('FAKE: Email cannot be empty.');
        }
        $employeeId = $config['employee_id'];
        $user = $this->usersByEmployeeId[$employeeId];
        foreach ($config as $attribute => $newValue) {
            $user[$attribute] = $newValue;
        }
        $this->usersByEmployeeId[$employeeId] = $user;
        return new User($user);
    }
}
