<?php
namespace Sil\Idp\IdSync\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Sil\Idp\IdSync\common\components\adapters\GoogleSheetsIdStore;
use Sil\Idp\IdSync\common\components\clients\GoogleSheetsClient;
use Sil\Idp\IdSync\common\models\User;
use Sil\PhpEnv\Env;

/**
 * Defines application features from the specific context.
 */
class GoogleSheetsIntegrationContext extends IdStoreIntegrationContextBase
{
    private $googleSheetsClient = null;
    
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
        $this->activeEmployeeId = Env::requireEnv('TEST_GOOGLE_SHEETS_EMPLOYEE_ID');
        $this->result = $this->idStore->getActiveUser($this->activeEmployeeId);
    }
    
    protected function getLastSyncedValueOfEachUser()
    {
        $lastSyncedValues = [];
        $googleSheetsClient = $this->getGoogleSheetsClient();
        $allUsersInfo = $googleSheetsClient->getAllUsersInfo();
        foreach ($allUsersInfo as $userInfo) {
            $employeeId = $userInfo[User::EMPLOYEE_ID];
            $lastSyncedValues[$employeeId] = $userInfo['last_synced'];
        }
        return $lastSyncedValues;
    }
    
    protected function getGoogleSheetsClient()
    {
        if ($this->googleSheetsClient === null) {
            $googleSheetsConfig = Env::getArrayFromPrefix('TEST_GOOGLE_SHEETS_CONFIG_');
            $this->googleSheetsClient = new GoogleSheetsClient($googleSheetsConfig);
        }
        return $this->googleSheetsClient;
    }

    /**
     * @When I update the last-synced value for a specific active user
     */
    public function iUpdateTheLastSyncedValueForASpecificActiveUser()
    {
        $this->activeEmployeeId = Env::requireEnv('TEST_GOOGLE_SHEETS_EMPLOYEE_ID');
        $this->idStore->updateSyncDatesIfSupported([$this->activeEmployeeId]);
    }
}
