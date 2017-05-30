<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use InvalidArgumentException;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\models\User;
use yii\helpers\Json;

class GoogleSheetsIdStore extends IdStoreBase
{
    /**
     * @var null|string Stores Application Name to use with Google_Client
     */
    public $applicationName = null;

    /**
     * @var null|string Stores JSON authentication credentials from Google
     */
    public $jsonAuthString = null;

    /**
     * @var null|string Stores Spreadsheet ID
     */
    public $spreadsheetId = null;

    /**
     * @var array<string> oAuth Scopes needed for reading/writing sheets
     */
    public $scopes = [\Google_Service_Sheets::SPREADSHEETS];


    /**
     * @var \Google_Client
     */
    private $client;

    /**
     * @var \Google_Service_Sheets
     */
    private $sheets;


    /**
     * Init and ensure required properties are set
     */
    public function init()
    {
        $requiredProperties = [
//            'applicationName',
//            'jsonAuthString',
//            'spreadsheetId',
        ];
        foreach ($requiredProperties as $requiredProperty) {
            if (empty($this->$requiredProperty)) {
                throw new InvalidArgumentException(sprintf(
                    'No %s was provided.',
                    $requiredProperty
                ), 1495648880);
            }
        }

        parent::init();
    }


    public function initGoogleClient()
    {
        $jsonCreds = Json::decode($this->jsonAuthString);
        $this->client = new \Google_Client();
        $this->client->setApplicationName($this->applicationName);
        $this->client->setScopes($this->scopes);
        $this->client->setAuthConfig($jsonCreds);
        $this->client->setAccessType('offline');
        $this->sheets = new \Google_Service_Sheets($this->client);
    }

    /**
     * Get the specified user's information. Note that inactive users will be
     * treated as non-existent users.
     *
     * @param string $employeeId The Employee ID.
     * @return User|null Information about the specified user, or null if no
     *     such active user was found.
     */
    public function getActiveUser(string $employeeId)
    {
        $allUsers = $this->getAllActiveUsers();
        foreach($allUsers as $user) {
            if ((string)$user->employeeId === (string)$employeeId) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Get information about each of the (active) users.
     *
     * @return User[] A list of Users.
     */
    public function getAllActiveUsers(): array
    {
        $allUsers = [];
        $start = 2;
        $howMany = 100;

        $hasAllUsers = false;
        while ( ! $hasAllUsers) {
            $batch = $this->getUsersFromSpreadsheet($start, $howMany);
            $allUsers = array_merge($allUsers, $batch);
            $start += $howMany;

            if (count($batch) < $howMany) {
                $hasAllUsers = true;
            }
        }

        return self::getAsUsers($allUsers);
    }


    public static function getIdBrokerFieldNames(): array
    {
        return [
            'employee_id' => User::EMPLOYEE_ID,
            'first_name' => User::FIRST_NAME,
            'last_name' => User::LAST_NAME,
            'display_name' => User::DISPLAY_NAME,
            'email' => User::EMAIL,
            'username' => User::USERNAME,
            'locked' => User::LOCKED,
            'active' => User::ACTIVE,
        ];
    }

    /**
     * Get a user-friendly name for this ID Store.
     *
     * @return string The name of the ID Store.
     */
    public function getIdStoreName(): string
    {
        return 'Google Sheets';
    }

    /**
     * Get a list of users who have had qualifying changes (name, email, locked,
     * activated, added) since the given Unix timestamp.
     *
     * @param int $unixTimestamp The date/time, as a Unix timestamp.
     * @return User[] A list of Users.
     */
    public function getUsersChangedSince(int $unixTimestamp): array
    {
        return $this->getAllActiveUsers();
    }

    /**
     * @param int $startRow
     * @param int $howMany
     * @return array
     */
    public function getUsersFromSpreadsheet(int $startRow = 2, int $howMany = 100): array
    {
        $users = [];
        $currentRow = $startRow;
        $range = sprintf('Users!A%s:H%s', $startRow, $startRow + $howMany - 1);
        $rows = $this->sheets->spreadsheets_values->get($this->spreadsheetId, $range, ['majorDimension' => 'ROWS']);
        if (isset($rows['values'])) {
            foreach ($rows['values'] as $user) {
                /*
                 * If first column is empty, consider it as no more records
                 */
                if (empty($user[0])) {
                    break;
                }

                $users[] = [
                    User::EMPLOYEE_ID => $user[0],
                    User::FIRST_NAME => $user[1],
                    User::LAST_NAME => $user[2],
                    User::DISPLAY_NAME => $user[3],
                    User::USERNAME => $user[4],
                    User::EMAIL => $user[5],
                    User::ACTIVE => $user[6] ?? 'yes',
                    User::LOCKED => $user[7] ?? 'no',
                ];

                /*
                 * Update last_synced column in spreadsheet
                 */
                $updateRange = 'I'.$currentRow;
                $updateBody = new \Google_Service_Sheets_ValueRange([
                    'range' => $updateRange,
                    'majorDimension' => 'ROWS',
                    'values' => ['values' => date('c')],
                ]);
                $this->sheets->spreadsheets_values->update(
                    $this->spreadsheetId,
                    $updateRange,
                    $updateBody,
                    ['valueInputOption' => 'USER_ENTERED']
                );

                $currentRow++;
            }
        }

        return $users;
    }

}