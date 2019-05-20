<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Sil\Idp\IdSync\common\components\adapters\WorkdayIdStore;
use Sil\PhpEnv\Env;

/**
 * Defines application features from the specific context.
 */
class WorkdayIntegrationContext extends IdStoreIntegrationContextBase
{
    public function __construct()
    {
        echo 'Testing integration with Sage People.' . PHP_EOL;
        parent::__construct();
    }
    
    /**
     * @Given I can make authenticated calls to the ID Store
     */
    public function iCanMakeAuthenticatedCallsToTheIdStore()
    {
        $sagePeopleApiUrl = Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_apiUrl');
        $sagePeopleUsername = Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_username');
        $sagePeoplePassword = Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_password');
        
        $this->idStore = new SagePeopleIdStore([
            'apiUrl' => $sagePeopleApiUrl,
            'username' => $sagePeopleUsername,
            'password' => $sagePeoplePassword,
        ]);
    }
    
    /**
     * @When I ask the ID Store for a specific active user
     */
    public function iAskTheIdStoreForASpecificActiveUser()
    {
        $this->activeEmployeeId = Env::requireEnv('TEST_SAGE_PEOPLE_EMPLOYEE_ID');
        $this->result = $this->idStore->getActiveUser($this->activeEmployeeId);
    }
}
