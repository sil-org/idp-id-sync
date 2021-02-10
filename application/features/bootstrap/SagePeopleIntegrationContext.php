<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Sil\Idp\IdSync\common\components\adapters\SagePeopleIdStore;
use Sil\PhpEnv\Env;

/**
 * Defines application features from the specific context.
 */
class SagePeopleIntegrationContext extends IdStoreIntegrationContextBase
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
        $this->idStore = new SagePeopleIdStore([
            'authUrl'         => Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_authUrl'),
            'queryUrl'        => Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_queryUrl'),
            'clientId'        => Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_clientId'),
            'clientSecret'    => Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_clientSecret'),
            'username'        => Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_username'),
            'password'        => Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_password'),
            'queryConditions' => Env::requireEnv('TEST_SAGE_PEOPLE_CONFIG_queryConditions'),
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
