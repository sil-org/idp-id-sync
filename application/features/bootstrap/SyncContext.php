<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;

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
     * @When I get the user's info from the ID Store and send it to the ID Broker
     */
    public function iGetTheUserSInfoFromTheIdStoreAndSendItToTheIdBroker()
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
     * @Given user exists in ID Store
     */
    public function userExistsInIdStore()
    {
        throw new PendingException();
    }

    /**
     * @Given user exists in ID Broker
     */
    public function userExistsInIdBroker()
    {
        throw new PendingException();
    }

    /**
     * @Given user info in ID Broker does not equal user info in ID Store
     */
    public function userInfoInIdBrokerDoesNotEqualUserInfoInIdStore()
    {
        throw new PendingException();
    }

    /**
     * @When I send user info in ID Store to ID Broker
     */
    public function iSendUserInfoInIdStoreToIdBroker()
    {
        throw new PendingException();
    }

    /**
     * @Then ID Broker response should indicate success
     */
    public function idBrokerResponseShouldIndicateSuccess()
    {
        throw new PendingException();
    }
}
