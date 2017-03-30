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
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /** @var \Sil\Idp\IdSync\common\interfaces\IdBrokerInterface */
    private $idBroker;
    
    /** @var \Sil\Idp\IdSync\common\interfaces\IdStoreInterface */
    private $idStore;
    
    /**
     * @Given the user exists in the ID Store
     */
    public function theUserExistsInTheIdStore()
    {
        throw new PendingException();
    }

    /**
     * @Given the user exists in the ID Broker
     */
    public function theUserExistsInTheIdBroker()
    {
        throw new PendingException();
    }

    /**
     * @When I get the user info from the ID Store and send it to the ID Broker
     */
    public function iGetTheUserInfoFromTheIdStoreAndSendItToTheIdBroker()
    {
        throw new PendingException();
    }

    /**
     * @Then the ID Broker response should indicate success
     */
    public function theIdBrokerResponseShouldIndicateSuccess()
    {
        throw new PendingException();
    }

    /**
     * @Given the user does not exist in the ID Broker
     */
    public function theUserDoesNotExistInTheIdBroker()
    {
        throw new PendingException();
    }

    /**
     * @Given the user does not exist in the ID Store
     */
    public function theUserDoesNotExistInTheIdStore()
    {
        throw new PendingException();
    }

    /**
     * @When I learn the user does not exist in the ID Store and I tell the ID Broker
     */
    public function iLearnTheUserDoesNotExistInTheIdStoreAndITellTheIdBroker()
    {
        throw new PendingException();
    }

    /**
     * @Then the ID Broker response should return an error
     */
    public function theIdBrokerResponseShouldReturnAnError()
    {
        throw new PendingException();
    }

    /**
     * @Given the user info in the ID Broker does not equal the user info in the ID Store
     */
    public function theUserInfoInTheIdBrokerDoesNotEqualTheUserInfoInTheIdStore()
    {
        throw new PendingException();
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
}
