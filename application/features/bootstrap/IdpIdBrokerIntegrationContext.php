<?php

namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Sil\Idp\IdSync\common\components\adapters\IdpIdBroker;
use Sil\Idp\IdSync\common\models\User;
use Sil\PhpEnv\Env;

/**
 * Defines application features from the specific context.
 */
class IdpIdBrokerIntegrationContext implements Context
{
    /**
     * @var IdpIdBroker
     */
    protected $idBroker;

    protected $oldPassword;
    protected $newPassword;

    protected $testUserData;

    /** @var User */
    protected $testUpdatedUser;

    protected $result;

    public function __construct()
    {
        require_once __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

        $this->idBroker = new IdpIdBroker([
            'baseUrl' => 'http://broker', // For tests. Matches docker container name.
            'accessToken' => Env::requireEnv('ID_BROKER_CONFIG_accessToken'),
            'assertValidIp' => false,
        ]);

        $this->testUserData = $this->generateDataForNewTestUser();
    }

    protected function generateDataForNewTestUser()
    {
        $uniqueId = uniqid();
        return [
            'employee_id' => (string)$uniqueId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'display_name' => 'Test User',
            'username' => 'user' . $uniqueId,
            'email' => 'user' . $uniqueId . '@example.com',
        ];
    }

    protected function generateDummyPassword()
    {
        return base64_encode(random_bytes(12));
    }

    /**
     * @Given an active user exists
     */
    public function anActiveUserExists()
    {
        $newUser = $this->idBroker->createUser($this->testUserData);
        Assert::assertNotNull($newUser);
    }

    /**
     * @Given that user is not active
     */
    public function thatUserIsNotActive()
    {
        // Deactivate the user.
        $this->idBroker->deactivateUser(
            $this->testUserData['employee_id']
        );

        // Confirm that it worked.
        $user = $this->idBroker->getUser(
            $this->testUserData['employee_id']
        );
        Assert::assertEquals('no', $user->getActive());
    }

    /**
     * @Then that user should now be active
     */
    public function thatUserShouldNowBeActive()
    {
        $user = $this->idBroker->getUser(
            $this->testUserData['employee_id']
        );
        Assert::assertEquals('yes', $user->getActive());
    }

    /**
     * @Then I should receive back information about that user
     */
    public function iShouldReceiveBackInformationAboutThatUser()
    {
        $numFieldsFound = 0;
        Assert::assertInstanceOf(User::class, $this->result);
        foreach ($this->result->toArray() as $key => $value) {
            if (array_key_exists($key, $this->testUserData)) {
                $numFieldsFound += 1;
                Assert::assertEquals($this->testUserData[$key], $value);
            }
        }
        Assert::assertGreaterThan(0, $numFieldsFound);
    }

    /**
     * @Given a user does not exist
     */
    public function aUserDoesNotExist()
    {
        $user = $this->idBroker->getUser($this->testUserData['employee_id']);
        Assert::assertNull($user);
    }

    /**
     * @When I create that user
     */
    public function iCreateThatUser()
    {
        $this->idBroker->createUser($this->testUserData);
    }

    /**
     * @Then that user should now exist
     */
    public function thatUserShouldNowExist()
    {
        $user = $this->idBroker->getUser($this->testUserData['employee_id']);
        Assert::assertNotNull($user);
        Assert::assertSame($this->testUserData['email'], $user->getEmail());
    }

    /**
     * @When I deactivate that user
     */
    public function iDeactivateThatUser()
    {
        $this->idBroker->deactivateUser(
            $this->testUserData['employee_id']
        );
    }

    /**
     * @Then that user should now NOT be active
     */
    public function thatUserShouldNowNotBeActive()
    {
        $user = $this->idBroker->getUser(
            $this->testUserData['employee_id']
        );
        Assert::assertEquals('no', $user->getActive());
    }

    /**
     * @When I get that user
     */
    public function iGetThatUser()
    {
        $this->result = $this->idBroker->getUser(
            $this->testUserData['employee_id']
        );
    }

    /**
     * @Given at least :number users exist
     */
    public function atLeastUsersExist($number)
    {
        if (! is_numeric($number)) {
            Assert::fail('Not given a number.');
        }
        for ($i = 0; $i < $number; $i++) {
            $createdUser = $this->idBroker->createUser(
                $this->generateDataForNewTestUser()
            );
            Assert::assertNotNull($createdUser);
        }
    }

    /**
     * @When I get the list of users
     */
    public function iGetTheListOfUsers()
    {
        $this->result = $this->idBroker->listUsers();
    }

    /**
     * @Then I should receive a list of at least :number users
     */
    public function iShouldReceiveAListOfAtLeastUsers($number)
    {
        if (! is_numeric($number)) {
            Assert::fail('Not given a number.');
        }
        Assert::assertGreaterThanOrEqual((int)$number, $this->result);
    }

    /**
     * @Then each entry in the resulting list should have user information
     */
    public function eachEntryInTheResultingListShouldHaveUserInformation()
    {
        foreach ($this->result as $user) {
            Assert::assertInstanceOf(User::class, $user);
            /* @var $user User */
            Assert::assertNotEmpty($user->getEmployeeId());
        }
    }

    /**
     * @When I update that user
     */
    public function iUpdateThatUser()
    {
        $this->testUpdatedUser = $this->idBroker->updateUser([
            'employee_id' => $this->testUserData['employee_id'],
            'display_name' => $this->testUserData['display_name'] . ', Jr.',
        ]);
        Assert::assertNotEquals(
            $this->testUserData['display_name'],
            $this->testUpdatedUser->getDisplayName()
        );
    }

    /**
     * @Then when I get that user I should receive the updated information
     */
    public function whenIGetThatUserIShouldReceiveTheUpdatedInformation()
    {
        $user = $this->idBroker->getUser($this->testUserData['employee_id']);
        Assert::assertSame(
            $this->testUpdatedUser->getDisplayName(),
            $user->getDisplayName()
        );
    }
}
