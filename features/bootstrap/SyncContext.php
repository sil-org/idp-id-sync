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
}
