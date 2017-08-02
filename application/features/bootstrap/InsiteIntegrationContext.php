<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Sil\Idp\IdSync\Behat\Context\IdStoreIntegrationContextBase;
use Sil\Idp\IdSync\common\components\adapters\InsiteIdStore;
use Sil\PhpEnv\Env;

/**
 * Defines application features from the specific context.
 */
class InsiteIntegrationContext extends IdStoreIntegrationContextBase
{
    public function __construct()
    {
        echo 'Testing integration with Insite.' . PHP_EOL;
        parent::__construct();
    }
    
    /**
     * @Given I can make authenticated calls to the ID Store
     */
    public function iCanMakeAuthenticatedCallsToTheIdStore()
    {
        $insiteApiKey = Env::requireEnv('TEST_INSITE_CONFIG_apiKey');
        $insiteApiSecret = Env::requireEnv('TEST_INSITE_CONFIG_apiSecret');
        $insiteBaseUrl = Env::requireEnv('TEST_INSITE_CONFIG_baseUrl');
        
        $this->idStore = new InsiteIdStore([
            'apiKey' => $insiteApiKey,
            'apiSecret' => $insiteApiSecret,
            'baseUrl' => $insiteBaseUrl,
        ]);
    }
    
    /**
     * @When I ask the ID Store for a specific active user
     */
    public function iAskTheIdStoreForASpecificActiveUser()
    {
        $this->activeEmployeeId = Env::requireEnv('TEST_INSITE_EMPLOYEE_ID');
        $this->result = $this->idStore->getActiveUser($this->activeEmployeeId);
    }
}
