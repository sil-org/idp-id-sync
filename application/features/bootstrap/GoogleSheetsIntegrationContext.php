<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Sil\Idp\IdSync\Behat\Context\IdStoreIntegrationContextBase;
use Sil\Idp\IdSync\common\components\adapters\GoogleSheetsIdStore;
use Sil\PhpEnv\Env;

/**
 * Defines application features from the specific context.
 */
class GoogleSheetsIntegrationContext extends IdStoreIntegrationContextBase
{
    public function __construct()
    {
        echo 'Testing integration with Google Sheets.' . PHP_EOL;
        parent::__construct();
    }
    
    /**
     * @Given I can make authenticated calls to the ID Store
     */
    public function iCanMakeAuthenticatedCallsToTheIdStore()
    {
        $googleSheetsConfig = Env::getArrayFromPrefix('TEST_GOOGLE_SHEETS_CONFIG_');
        $this->idStore = new GoogleSheetsIdStore($googleSheetsConfig);
    }
    
    /**
     * @When I ask the ID Store for a specific active user
     */
    public function iAskTheIdStoreForASpecificActiveUser()
    {
        $employeeId = Env::requireEnv('TEST_GOOGLE_SHEETS_EMPLOYEE_ID');
        $this->result = $this->idStore->getActiveUser($employeeId);
    }
}
