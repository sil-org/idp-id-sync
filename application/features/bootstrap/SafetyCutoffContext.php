<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Context;
use Exception;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;
use Sil\Idp\IdSync\common\sync\Synchronizer;
use Sil\Idp\IdSync\common\components\adapters\fakes\FakeIdBroker;
use Sil\Idp\IdSync\common\components\adapters\fakes\FakeIdStore;
use Sil\Idp\IdSync\common\components\notify\ConsoleNotifier;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\models\User;
use Sil\Psr3Adapters\Psr3ConsoleLogger;
use yii\helpers\Json;

/**
 * Defines application features from the specific context.
 */
class SafetyCutoffContext implements Context
{
    /** @var Exception */
    private $exceptionThrown = null;
    
    /** @var IdBrokerInterface */
    private $idBroker;
    
    /** @var IdStoreInterface */
    private $idStore;
    
    /** @var LoggerInterface */
    protected $logger;
    
    /** @var NotifierInterface */
    protected $notifier;
    
    private $tempMaxDeactivationsPercent = null;
    
    /**
     * @Then an exception SHOULD have been thrown
     */
    public function anExceptionShouldHaveBeenThrown()
    {
        Assert::assertNotNull(
            $this->exceptionThrown,
            "An exception should have been thrown, but wasn't"
        );
    }
    
    /**
     * @Then an exception should NOT have been thrown
     */
    public function anExceptionShouldNotHaveBeenThrown()
    {
        $possibleException = $this->exceptionThrown ?? new Exception();
        Assert::assertNotInstanceOf(Exception::class, $this->exceptionThrown, sprintf(
            'Unexpected exception (%s): %s',
            $possibleException->getCode(),
            $possibleException->getMessage()
        ));
    }
    
    protected function createSynchronizer()
    {
        return new Synchronizer(
            $this->idStore,
            $this->idBroker,
            $this->logger,
            $this->notifier
        );
    }
    
    /**
     * @param array $activeUsers
     * @return FakeIdStore
     */
    protected function getFakeIdStore(array $activeUsers = [])
    {
        return new FakeIdStore($activeUsers);
    }
    
    /**
     * @When I sync all the users from the ID Store to the ID Broker
     */
    public function iSyncAllTheUsersFromTheIdStoreToTheIdBroker()
    {
        try {
            $synchronizer = $this->createSynchronizer();
            if ($this->tempMaxDeactivationsPercent !== null) {
                $synchronizer->syncAll($this->tempMaxDeactivationsPercent);
            } else {
                $synchronizer->syncAll();
            }
        } catch (Exception $e) {
            $this->exceptionThrown = $e;
        }
    }
    
    /**
     * @Given the cutoff for deactivations is :number
     */
    public function theCutoffForDeactivationsIs($number)
    {
        $this->tempMaxDeactivationsPercent = $number;
    }
    
    /**
     * @Given :number users are active in the ID Broker
     */
    public function usersAreActiveInTheIdBroker($number)
    {
        $idBrokerUsers = [];
        for ($i = 1; $i <= $number; $i++) {
            $tempEmployeeId = 10000 + $i;
            $idBrokerUsers[$tempEmployeeId] = [
                User::EMPLOYEE_ID => (string)$tempEmployeeId,
                User::DISPLAY_NAME => 'Person ' . $i,
                User::USERNAME => 'person_' . $i,
                User::FIRST_NAME => 'Person',
                User::LAST_NAME => (string)$i,
                User::EMAIL => 'person_' . $i . '@example.com',
                User::ACTIVE => 'yes',
            ];
        }
        
        $this->idBroker = new FakeIdBroker($idBrokerUsers);
    }
    
    /**
     * @Given (only) :number users are active in the ID Store
     */
    public function usersAreActiveInTheIdStore($number)
    {
        $activeIdStoreUsers = [];
        for ($i = 1; $i <= $number; $i++) {
            $tempEmployeeId = 10000 + $i;
            $activeIdStoreUsers[$tempEmployeeId] = [
                'employeenumber' => (string)$tempEmployeeId,
                'displayname' => 'Person ' . $i,
                'username' => 'person_' . $i,
                'firstname' => 'Person',
                'lastname' => (string)$i,
                'email' => 'person_' . $i . '@example.com',
            ];
        }
        $this->idStore = $this->getFakeIdStore($activeIdStoreUsers);
    }
    
    /**
     * @Given :number users are active in the ID Store and are inactive in the ID Broker
     */
    public function usersAreActiveInTheIdStoreAndAreInactiveInTheIdBroker($number)
    {
        $this->usersAreActiveInTheIdStore($number);
        
        $idBrokerUsers = [];
        foreach ($this->idStore->getAllActiveUsers() as $user) {
            $user->active = 'no';
            $idBrokerUsers[$user->employeeId] = $user->toArray();
        }
        $this->idBroker = new FakeIdBroker($idBrokerUsers);
    }
}
