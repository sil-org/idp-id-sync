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
     * @When I request the user info from the ID Store
     */
    public function iRequestTheUserInfoFromTheIdStore()
    {
        throw new PendingException();
    }

    /**
     * @Then the response should provide user info
     */
    public function theResponseShouldProvideUserInfo()
    {
        throw new PendingException();
    }
}
