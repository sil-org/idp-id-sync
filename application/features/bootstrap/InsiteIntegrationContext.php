<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Sil\Idp\IdSync\common\sync\Synchronizer;
use Sil\Idp\IdSync\common\components\adapters\fakes\FakeIdBroker;
use Sil\Idp\IdSync\common\components\adapters\InsiteIdStore;
use Sil\PhpEnv\Env;
use yii\helpers\Json;

/**
 * Defines application features from the specific context.
 */
class InsiteIntegrationContext implements Context
{
    /** @var InsiteIdStore */
    private $idStore;
    
    private $result;
    
    public function __construct()
    {
        require_once __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
    }
    
    protected function assertArrayHasFields($array, $fields)
    {
        foreach ($fields as $field) {
            Assert::assertArrayHasKey($field, $array, sprintf(
                'Array: %s',
                var_export($array, true)
            ));
        }
    }
    
    protected function getExpectedFields()
    {
        return array_keys(InsiteIdStore::getIdBrokerFieldNames());
    }
    
    /**
     * @Given I can make authenticated calls to Insite
     */
    public function iCanMakeAuthenticatedCallsToInsite()
    {
        $insiteApiKey = Env::requireEnv('TEST_INSITE_API_KEY');
        $insiteApiSecret = Env::requireEnv('TEST_INSITE_API_SECRET');
        $insiteBaseUrl = Env::requireEnv('TEST_INSITE_BASE_URL');
        
        $this->idStore = new InsiteIdStore([
            'apiKey' => $insiteApiKey,
            'apiSecret' => $insiteApiSecret,
            'baseUrl' => $insiteBaseUrl,
        ]);
    }
    
    /**
     * @When I ask Insite for a specific active user
     */
    public function iAskInsiteForASpecificActiveUser()
    {
        $employeeId = Env::requireEnv('TEST_INSITE_EMPLOYEE_ID');
        $this->result = $this->idStore->getActiveUser($employeeId);
    }
    
    /**
     * @Then I should get back information about that user
     */
    public function iShouldGetBackInformationAboutThatUser()
    {
        Assert::assertNotNull($this->result);
        $this->assertArrayHasFields($this->result, $this->getExpectedFields());
    }
    
    /**
     * @When I ask Insite for all active users
     */
    public function iAskInsiteForAllActiveUsers()
    {
        $this->result = $this->idStore->getAllActiveUsers();
    }
    
    /**
     * @Then I should get back a list of information about active users
     */
    public function iShouldGetBackAListOfInformationAboutActiveUsers()
    {
        Assert::assertNotNull($this->result);
        $expectedFields = $this->getExpectedFields();
        foreach ($this->result as $userInfo) {
            $numFieldsFound = 0;
            foreach ($expectedFields as $field) {
                if (array_key_exists($field, $userInfo)) {
                    $numFieldsFound += 1;
                }
            }
            
            /* As long as we find some fields about each user, consider this
             * successful.  */
            Assert::assertGreaterThan(3, $numFieldsFound);
        }
    }
    
    /**
     * @When I ask Insite for all users changed since a specific point in time
     */
    public function iAskInsiteForAllUsersChangedSinceASpecificPointInTime()
    {
        $this->result = $this->idStore->getUsersChangedSince(1489764017);
    }
    
    /**
     * @Then I should get back a list of information about changed users
     */
    public function iShouldGetBackAListOfInformationAboutChangedUsers()
    {
        Assert::assertNotNull($this->result);
        foreach ($this->result as $userInfo) {
            Assert::assertArrayHasKey('employeeNumber', $userInfo);
        }
    }
}
