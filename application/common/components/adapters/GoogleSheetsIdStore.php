<?php

namespace Sil\Idp\IdSync\common\components\adapters;

use Sil\Idp\IdSync\common\components\clients\GoogleSheetsClient;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\models\User;

class GoogleSheetsIdStore extends IdStoreBase
{
    /**
     * @var null|string The Application Name to use with Google_Client.
     */
    public $applicationName = null;

    /**
     * @var null|string The path to the JSON file with authentication
     *     credentials from Google.
     */
    public $jsonAuthFilePath = null;

    /**
     * @var null|string The JSON authentication credentials from Google.
     */
    public $jsonAuthString = null;

    /**
     * @var null|string The Spreadsheet ID.
     */
    public $spreadsheetId = null;

    /**
     * @var array<string> OAuth Scopes needed for reading/writing sheets.
     */
    public $scopes = [\Google_Service_Sheets::SPREADSHEETS];

    /**
     * @var GoogleSheetsClient
     */
    private $googleSheetsClient = null;

    /**
     * Init and ensure required properties are set
     */
    public function init()
    {
        $this->googleSheetsClient = new GoogleSheetsClient([
            'applicationName' => $this->applicationName,
            'jsonAuthFilePath' => $this->jsonAuthFilePath,
            'jsonAuthString' => $this->jsonAuthString,
            'spreadsheetId' => $this->spreadsheetId,
            'scopes' => $this->scopes,
        ]);

        parent::init();
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
        foreach ($allUsers as $user) {
            if ((string)$user->getEmployeeId() === (string)$employeeId) {
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
        $allUsersInfo = $this->googleSheetsClient->getAllUsersInfo();

        $allActiveUsersInfo = array_filter(
            $allUsersInfo,
            function ($user) {
                return ($user[User::ACTIVE] === 'yes');
            }
        );

        return array_map(
            function ($entry) {
                // Unset 'active', since ID Stores only return active users.
                unset($entry[User::ACTIVE]);

                // Convert the resulting user info to a User.
                return self::getAsUser($entry);
            },
            $allActiveUsersInfo
        );
    }

    public static function getFieldNameMap(): array
    {
        return [
            // No 'active' needed, since all ID Store records returned are active.
            'employee_id' => User::EMPLOYEE_ID,
            'first_name' => User::FIRST_NAME,
            'last_name' => User::LAST_NAME,
            'display_name' => User::DISPLAY_NAME,
            'email' => User::EMAIL,
            'username' => User::USERNAME,
            'locked' => User::LOCKED,
            'require_mfa' => User::REQUIRE_MFA,
            'manager_email' => User::MANAGER_EMAIL,
            'personal_email' => User::PERSONAL_EMAIL,
            'groups' => User::GROUPS,

            User::HR_CONTACT_NAME => User::HR_CONTACT_NAME,
            User::HR_CONTACT_EMAIL => User::HR_CONTACT_EMAIL,
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
        /* NOTE: For simplicity's sake (since this Google Sheets approach is
         *       intended for smaller sets of users), simply resync all active
         *       users when trying to do an incremental sync here.  */
        return $this->getAllActiveUsers();
    }

    /**
     * {@inheritdoc}
     */
    public function updateSyncDatesIfSupported(array $employeeIds)
    {
        $this->googleSheetsClient->updateSyncDatesFor($employeeIds);
    }
}
