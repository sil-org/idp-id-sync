<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Sil\Idp\IdSync\common\models\User;

/**
 * Defines application features from the specific context.
 */
class IdStoreIntegrationContextBase implements Context
{
    /** @var IdStoreInterface */
    protected $idStore;
    
    protected $activeEmployeeId;
    protected $lastSyncedValues = [];
    protected $result;
    
    public function __construct()
    {
        require_once __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
    }
    
    /**
     * @Then I should get back information about that user
     */
    public function iShouldGetBackInformationAboutThatUser()
    {
        Assert::assertNotNull($this->result, sprintf(
            'Did not find user %s. Are you sure that they exist and are active?',
            var_export($this->activeEmployeeId, true)
        ));
        Assert::assertInstanceOf(User::class, $this->result);
    }
    
    /**
     * @When I ask the ID Store for all active users
     */
    public function iAskTheIdStoreForAllActiveUsers()
    {
        $this->result = $this->idStore->getAllActiveUsers();
    }
    
    /**
     * @Then I should get back a list of information about active users
     */
    public function iShouldGetBackAListOfInformationAboutActiveUsers()
    {
        Assert::assertNotNull($this->result);
        Assert::assertNotEmpty($this->result);
        Assert::assertTrue(is_array($this->result));
        foreach ($this->result as $user) {
            Assert::assertInstanceOf(User::class, $user);
        }
    }
    
    /**
     * @When I ask the ID Store for all users changed since a specific point in time
     */
    public function iAskTheIdStoreForAllUsersChangedSinceASpecificPointInTime()
    {
        $this->result = $this->idStore->getUsersChangedSince(strtotime('-2 months'));
    }
    
    /**
     * @Then I should get back a list of information about changed users
     */
    public function iShouldGetBackAListOfInformationAboutChangedUsers()
    {
        Assert::assertNotNull($this->result);
        Assert::assertNotEmpty($this->result);
        Assert::assertTrue(is_array($this->result));
        foreach ($this->result as $user) {
            Assert::assertInstanceOf(User::class, $user);
        }
    }
    
    /**
     * @Given I have a record of each user's last-synced value
     */
    public function iHaveARecordOfEachUsersLastSyncedValue()
    {
        $this->lastSyncedValues = $this->getAttributeForEachUser('last_synced');
        Assert::assertNotEmpty($this->lastSyncedValues);
    }
    
    /**
     * @Given those last-synced values are all in the past or empty
     */
    public function thoseLastSyncedValuesAreAllInThePastOrEmpty()
    {
        $nowTimestamp = time();
        foreach ($this->lastSyncedValues as $lastSyncedValue) {
            if (! empty($lastSyncedValue)) {
                $lastSyncedTimestamp = strtotime($lastSyncedValue);
                Assert::assertNotFalse($lastSyncedTimestamp);
                Assert::assertLessThan($nowTimestamp, $lastSyncedTimestamp);
            }
        }
    }
    
    /**
     * @Then NONE of the users' last-synced values should have changed
     */
    public function noneOfTheUsersLastSyncedValuesShouldHaveChanged()
    {
        $newLastSyncedValues = $this->getAttributeForEachUser('last_synced');
        foreach ($this->lastSyncedValues as $employeeId => $oldLastSyncedValue) {
            Assert::assertEquals(
                $oldLastSyncedValue,
                $newLastSyncedValues[$employeeId]
            );
        }
    }
    
    /**
     * Get a specific attribute's value for each user. The keys will be the
     * Employee ID, and the values will be the attribute's value.
     *
     * EXAMPLE:
     * Calling `getAttributeForEachUser('last_synced')` will return an
     * array similar to this:
     *
     *     [
     *         123 => '2018-12-21T20:53:14+00:00',
     *         1234 => '2018-12-21T20:53:14+00:00',
     *     ]
     *
     *
     * @param string $attributeName The name of the desired attribute.
     * @return array<string,string>
     * @throws \Exception
     */
    protected function getAttributeForEachUser(string $attributeName)
    {
        // NOTE: Override this method in the applicable subclasses.
        
        throw new \Exception(sprintf(
            'You have not yet implemented the %s() function on the %s class.',
            __FUNCTION__,
            static::class
        ));
    }
    
    /**
     * @Then ONLY that user's last-synced value should have changed
     */
    public function onlyThatUsersLastSyncedValueShouldHaveChanged()
    {
        $newLastSyncedValues = $this->getAttributeForEachUser('last_synced');
        Assert::assertGreaterThan(
            1,
            count($newLastSyncedValues),
            "To prove that other users' last-synced dates did NOT change, this test requires more than 1 user."
        );
        
        foreach ($newLastSyncedValues as $employeeId => $newLastSyncedValue) {
            if ($employeeId == $this->activeEmployeeId) {
                Assert::assertNotEquals(
                    $this->lastSyncedValues[$employeeId],
                    $newLastSyncedValues[$employeeId]
                );
            } else {
                Assert::assertEquals(
                    $this->lastSyncedValues[$employeeId],
                    $newLastSyncedValues[$employeeId]
                );
            }
        }
    }
    
    /**
     * @When I update the last-synced value for every user
     */
    public function iUpdateTheLastSyncedValueForEveryUser()
    {
        $allEmployeeIds = array_keys($this->lastSyncedValues);
        $this->idStore->updateSyncDatesIfSupported($allEmployeeIds);
    }
    
    /**
     * @Then every users' last-synced values should have changed
     */
    public function everyUsersLastSyncedValuesShouldHaveChanged()
    {
        $newLastSyncedValues = $this->getAttributeForEachUser('last_synced');
        foreach ($this->lastSyncedValues as $employeeId => $oldLastSyncedValue) {
            Assert::assertNotEquals(
                $oldLastSyncedValue,
                $newLastSyncedValues[$employeeId]
            );
        }
    }
}
