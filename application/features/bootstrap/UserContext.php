<?php
namespace Sil\Idp\IdSync\Behat\Context;

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
     * @Given I create a User with a :field value of 0
     */
    public function iCreateAUserWithAValueOf0($field)
    {
        $this->user = $this->createUserWith($field, 0);
    }

    /**
     * @Given I create a User with a :field value of 1
     */
    public function iCreateAUserWithAValueOf1($field)
    {
        $this->user = $this->createUserWith($field, 1);
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
        Assert::assertNull($this->result[$field]);
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
    
    /**
     * @Given I create a User with a :field value of :value and an Employee ID
     */
    public function iCreateAUserWithAFieldValueOfValueAndAnEmployeeId($field, $value)
    {
        $this->user = new User([
            $field => $value,
            User::EMPLOYEE_ID => '12345',
        ]);
    }
    
    /**
     * @Then the result should ONLY contain :field and an Employee ID
     */
    public function theResultShouldOnlyContainFieldAndAnEmployeeId($field)
    {
        Assert::assertArrayHasKey($field, $this->result);
        unset($this->result[$field]);
        
        Assert::assertArrayHasKey(User::EMPLOYEE_ID, $this->result);
        unset($this->result[User::EMPLOYEE_ID]);
        
        Assert::assertCount(0, $this->result, sprintf(
            "The array unexpectedly contained the following entries: \n%s",
            var_export($this->result, true)
        ));
    }
}
