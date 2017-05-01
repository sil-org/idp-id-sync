<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Sil\Idp\IdSync\common\sync\Synchronizer;
use Sil\Idp\IdSync\common\components\adapters\fakes\FakeIdBroker;
use Sil\Idp\IdSync\common\components\adapters\fakes\FakeIdStore;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use yii\helpers\Json;

/**
 * Defines application features from the specific context.
 */
class SyncContext implements Context
{
    /** @var IdBrokerInterface */
    private $idBroker;
    
    /** @var IdStoreInterface */
    private $idStore;
    
    private $tempEmployeeId;
    
    private $tempUserChanges = [];
    
    /**
     * @param array $activeUsers
     * @return FakeIdStore
     */
    protected function getFakeIdStore(array $activeUsers = [])
    {
        return new FakeIdStore($activeUsers, $this->tempUserChanges);
    }
    
    /**
     * @Given a specific user exists in the ID Store
     */
    public function aSpecificUserExistsInTheIdStore()
    {
        $tempIdStoreUser = [
            'employeenumber' => '10001',
            'displayname' => 'Person One',
            'username' => 'person_one',
            'firstname' => 'Person',
            'lastname' => 'One',
            'email' => 'person_one@example.com',
        ];
        
        $this->tempEmployeeId = $tempIdStoreUser['employeenumber'];
        
        $this->idStore = $this->getFakeIdStore([
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
        Assert::assertNotNull($this->idBroker->getUser($this->tempEmployeeId));
    }

    /**
     * @Then the user info in the ID Broker and the ID Store should match
     */
    public function theUserInfoInTheIdBrokerAndTheIdStoreShouldMatch()
    {
        $userInfoFromIdBroker = $this->idBroker->getUser($this->tempEmployeeId);
        $userInfoFromIdStore = $this->idStore->getActiveUser($this->tempEmployeeId);
        
        foreach ($userInfoFromIdStore as $attribute => $value) {
            Assert::assertSame($value, $userInfoFromIdBroker[$attribute], sprintf(
                "Expected the ID Broker data...\n%s\n... to match the ID Store data...\n%s",
                var_export($userInfoFromIdBroker, true),
                var_export($userInfoFromIdStore, true)
            ));
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
        $this->idStore = $this->getFakeIdStore([]);
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
        $idBrokerUser = $this->idBroker->getUser($this->tempEmployeeId);
        Assert::assertSame('no', $idBrokerUser['active']);
    }

    /**
     * @Then the user should not exist in the ID Broker
     */
    public function theUserShouldNotExistInTheIdBroker()
    {
        Assert::assertNull($this->idBroker->getUser($this->tempEmployeeId));
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
     * @Given ONLY the following users are active in the ID Store:
     */
    public function onlyTheFollowingUsersAreActiveInTheIdStore(TableNode $table)
    {
        $idStoreActiveUsers = [];
        foreach ($table as $row) {
            $idStoreActiveUsers[$row['employeenumber']] = $row;
        }
        $this->idStore = $this->getFakeIdStore($idStoreActiveUsers);
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
        $desiredFields = null;
        foreach ($table as $row) {
            $desiredFields = array_keys($row);
            break;
        }
        
        $actualUsersInfo = $this->getIdBrokerUsers($desiredFields);
        Assert::assertJsonStringEqualsJsonString(
            Json::encode($table),
            Json::encode($actualUsersInfo)
        );
    }
    
    protected function getIdBrokerUsers($desiredFields = null)
    {
        return $this->idBroker->listUsers($desiredFields);
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
        $this->idStore = $this->getFakeIdStore([]);
    }

    /**
     * @Given the ID Store has the following log of when users were changed:
     */
    public function theIdStoreHasTheFollowingLogOfWhenUsersWereChanged(TableNode $table)
    {
        foreach ($table as $row) {
            $this->tempUserChanges[] = [
                'changedat' => $row['changedat'],
                'employeenumber' => $row['employeenumber'],
            ];
        }
    }

    /**
     * @When I ask the ID Store for the list of users changed since :timestamp and sync them
     */
    public function iAskTheIdStoreForTheListOfUsersChangedSinceAndSyncThem($timestamp)
    {
        $changedUsers = $this->idStore->getUsersChangedSince($timestamp);
        $employeeIds = [];
        foreach ($changedUsers as $changedUser) {
            $employeeIds[] = $changedUser['employeenumber'];
        }
        $synchronizer = new Synchronizer($this->idStore, $this->idBroker);
        $synchronizer->syncUsers($employeeIds);
    }
}
