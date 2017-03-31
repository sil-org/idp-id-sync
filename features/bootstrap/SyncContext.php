<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Sil\Idp\IdSync\common\sync\Synchronizer;
use Sil\Idp\IdSync\tests\fakes\FakeIdBroker;
use Sil\Idp\IdSync\tests\fakes\FakeIdStore;
use yii\helpers\Json;

/**
 * Defines application features from the specific context.
 */
class SyncContext implements Context
{
    /** @var \Sil\Idp\IdSync\common\interfaces\IdBrokerInterface */
    private $idBroker;
    
    /** @var \Sil\Idp\IdSync\common\interfaces\IdStoreInterface */
    private $idStore;
    
    private $tempEmployeeId;
    
    /**
     * @Given a specific user exists in the ID Store
     */
    public function aSpecificUserExistsInTheIdStore()
    {
        $tempIdStoreUser = [
            'employeeNumber' => '10001',
            'displayName' => 'Person One',
            'username' => 'person_one',
        ];
        $this->tempEmployeeId = $tempIdStoreUser['employeeNumber'];
        $this->idStore = new FakeIdStore([
            $this->tempEmployeeId => $tempIdStoreUser,
        ]);
    }

    /**
     * @Given the user exists in the ID Broker
     */
    public function theUserExistsInTheIdBroker()
    {
        $tempUserForIdBroker = $this->idStore->getActiveUser(
            $this->tempEmployeeId
        );
        
        $this->idBroker = new FakeIdBroker([
            $tempUserForIdBroker['employee_id'] => $tempUserForIdBroker,
        ]);
    }

    /**
     * @When I get the user info from the ID Store and send it to the ID Broker
     */
    public function iGetTheUserInfoFromTheIdStoreAndSendItToTheIdBroker()
    {
        $synchronizer = new Synchronizer($this->idStore, $this->idBroker);
        $synchronizer->syncUser($this->tempEmployeeId);
    }

    /**
     * @Then the user should exist in the ID Broker
     */
    public function theUserShouldExistInTheIdBroker()
    {
        Assert::assertNotNull($this->idBroker->getUser([
            'employee_id' => $this->tempEmployeeId
        ]));
    }

    /**
     * @Then the user info in the ID Broker and the ID Store should match
     */
    public function theUserInfoInTheIdBrokerAndTheIdStoreShouldMatch()
    {
        $userInfoFromIdBroker = $this->idBroker->getUser([
            'employee_id' => $this->tempEmployeeId,
        ]);
        $userInfoFromIdStore = $this->idStore->getActiveUser($this->tempEmployeeId);
        
        foreach ($userInfoFromIdStore as $attribute => $value) {
            Assert::assertSame($value, $userInfoFromIdBroker[$attribute]);
        }
    }

    /**
     * @Given the user does not exist in the ID Broker
     */
    public function theUserDoesNotExistInTheIdBroker()
    {
        $this->idBroker = new FakeIdBroker();
    }

    /**
     * @Given the user does not exist in the ID Store
     */
    public function theUserDoesNotExistInTheIdStore()
    {
        $this->idStore = new FakeIdStore();
    }

    /**
     * @When I learn the user does not exist in the ID Store and I tell the ID Broker
     */
    public function iLearnTheUserDoesNotExistInTheIdStoreAndITellTheIdBroker()
    {
        $synchronizer = new Synchronizer($this->idStore, $this->idBroker);
        $synchronizer->syncUser($this->tempEmployeeId);
    }

    /**
     * @Then the user should be inactive in the ID Broker
     */
    public function theUserShouldBeInactiveInTheIdBroker()
    {
        $idBrokerUser = $this->idBroker->getUser([
            'employee_id' => $this->tempEmployeeId,
        ]);
        Assert::assertSame('no', $idBrokerUser['active']);
    }

    /**
     * @Then the user should not exist in the ID Broker
     */
    public function theUserShouldNotExistInTheIdBroker()
    {
        Assert::assertNull($this->idBroker->getUser([
            'employee_id' => $this->tempEmployeeId,
        ]));
    }

    /**
     * @Given the user info in the ID Broker does not match the user info in the ID Store
     */
    public function theUserInfoInTheIdBrokerDoesNotMatchTheUserInfoInTheIdStore()
    {
        $userInfoFromIdStore = $this->idStore->getActiveUser($this->tempEmployeeId);
        $this->idBroker->updateUser([
            'employee_id' => $userInfoFromIdStore['employee_id'],
            'display_name' => $userInfoFromIdStore['display_name'] . ' Jr.',
        ]);
    }

    /**
     * @Given ONLY the following users exist in the ID Store:
     */
    public function onlyTheFollowingUsersExistInTheIdStore(TableNode $table)
    {
        $idStoreUsers = [];
        foreach ($table as $row) {
            $idStoreUsers[$row['employeeNumber']] = $row;
        }
        $this->idStore = new FakeIdStore($idStoreUsers);
    }

    /**
     * @Given ONLY the following users exist in the ID Broker:
     */
    public function onlyTheFollowingUsersExistInTheIdBroker(TableNode $table)
    {
        $idBrokerUsers = [];
        foreach ($table as $row) {
            $idBrokerUsers[$row['employee_id']] = $row;
        }
        $this->idBroker = new FakeIdBroker($idBrokerUsers);
    }

    /**
     * @When I sync all the users from the ID Store to the ID Broker
     */
    public function iSyncAllTheUsersFromTheIdStoreToTheIdBroker()
    {
        $synchronizer = new Synchronizer($this->idStore, $this->idBroker);
        $synchronizer->syncAll();
    }

    /**
     * @Then ONLY the following users should exist in the ID Broker:
     */
    public function onlyTheFollowingUsersShouldExistInTheIdBroker(TableNode $table)
    {
        Assert::assertJsonStringEqualsJsonString(
            Json::encode($table), // Expected (according to feature file)
            Json::encode($this->getIdBrokerUsers()) // Actual
        );
    }
    
    protected function getIdBrokerUsers()
    {
        $userList = $this->idBroker->listUsers();
        $usersInfo = [];
        foreach ($userList as $userPartialInfo) {
            $usersInfo[] = $this->idBroker->getUser([
                'employee_id' => $userPartialInfo['employee_id'],
            ]);
        }
        return $usersInfo;
    }

    /**
     * @Given a specific user exists in the ID Broker
     */
    public function aSpecificUserExistsInTheIdBroker()
    {
        $tempIdBrokerUser = [
            'employee_id' => '10001',
            'display_name' => 'Person One',
            'username' => 'person_one',
        ];
        $this->tempEmployeeId = $tempIdBrokerUser['employee_id'];
        $this->idBroker = new FakeIdBroker([
            $this->tempEmployeeId => $tempIdBrokerUser,
        ]);
    }

    /**
     * @Given a specific user does not exist in the ID Store
     */
    public function aSpecificUserDoesNotExistInTheIdStore()
    {
        $this->tempEmployeeId = '10005';
        $this->idStore = new FakeIdStore();
    }
}
