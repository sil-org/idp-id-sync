<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Sil\Idp\IdSync\common\models\User;

/**
 * Defines application features from the specific context.
 */
class UserContext implements Context
{
    /** @var User */
    protected $user;
    
    /** @var array */
    protected $result;
    
    protected function createUserWith($field, $value)
    {
        return new User(array_merge([
            // Start with default values for required fields:
            'employee_id' => '123'
        ], [
            // Overwrite with given value field/value:
            $field => $value,
        ]));
    }
    
    /**
     * @Given I create a User with a :field value of ':input'
     */
    public function iCreateAUserWithAValueOf($field, $input)
    {
        $this->user = $this->createUserWith($field, $input);
    }

    /**
     * @When I get the info from that User
     */
    public function iGetTheInfoFromThatUser()
    {
        $this->result = $this->user->toArray();
    }

    /**
     * @Then the :field value should be ':expected'
     */
    public function theValueShouldBe($field, $expected)
    {
        $actual = $this->result[$field];
        Assert::assertSame($expected, $actual, sprintf(
            "Expected: %s\nActual: %s",
            var_export($expected, true),
            var_export($actual, true)
        ));
    }

    /**
     * @Then the :field value should be null
     */
    public function theValueShouldBeNull($field)
    {
        Assert::assertArrayNotHasKey($field, $this->result);
    }

    /**
     * @Given I create a User with a :field value of false
     */
    public function iCreateAUserWithAValueOfFalse($field)
    {
        $this->user = $this->createUserWith($field, false);
    }

    /**
     * @Given I create a User with a :field value of true
     */
    public function iCreateAUserWithAValueOfTrue($field)
    {
        $this->user = $this->createUserWith($field, true);
    }

    /**
     * @Given I create a User with a :field value of null
     */
    public function iCreateAUserWithAValueOfNull($field)
    {
        $this->user = $this->createUserWith($field, null);
    }
}
