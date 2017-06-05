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
        Assert::assertNotNull($this->result);
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
        foreach ($this->result as $user) {
            Assert::assertInstanceOf(User::class, $user);
        }
    }
    
    /**
     * @When I ask the ID Store for all users changed since a specific point in time
     */
    public function iAskTheIdStoreForAllUsersChangedSinceASpecificPointInTime()
    {
        $this->result = $this->idStore->getUsersChangedSince(1489764017);
    }
    
    /**
     * @Then I should get back a list of information about changed users
     */
    public function iShouldGetBackAListOfInformationAboutChangedUsers()
    {
        Assert::assertNotNull($this->result);
        Assert::assertNotEmpty($this->result);
        foreach ($this->result as $user) {
            Assert::assertInstanceOf(User::class, $user);
        }
    }
}
