<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use PHPUnit\Framework\Assert;
use Sil\Idp\IdSync\common\components\notify\FakeEmailNotifier;
use Sil\Idp\IdSync\common\models\User;

/**
 * Defines application features from the specific context.
 */
class NotificationContext extends SyncContext
{
    /** @var array */
    private $users;

    public function __construct()
    {
        parent::__construct();
        $this->notifier = new FakeEmailNotifier();
    }

    /**
     * @Given at least one user has no email address
     */
    public function atLeastOneUserHasNoEmailAddress()
    {
        $this->users[] = new User(['employee_id'=>1]);
    }

    /**
     * @When I call the sendMissingEmailNotice function
     */
    public function iCallTheSendmissingemailnoticeFunction()
    {
        $this->notifier->sendMissingEmailNotice($this->users);
    }

    /**
     * @Then an email is sent
     */
    public function anEmailIsSent()
    {
        Assert::assertNotEmpty($this->notifier->emailsSent);
    }

    /**
     * @Given a specific user exists in the ID Store without an email address
     */
    public function aSpecificUserExistsInTheIdStoreWithoutAnEmailAddress()
    {
        $tempIdStoreUserInfo = [
            'employeenumber' => '10001',
            'displayname' => 'Person One',
            'username' => 'person_one',
            'firstname' => 'Person',
            'lastname' => 'One',
        ];

        $this->makeFakeIdStoreWithUser($tempIdStoreUserInfo);
    }
}
