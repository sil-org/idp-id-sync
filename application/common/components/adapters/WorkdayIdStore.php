<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\models\User;
use yii\helpers\Json;

class WorkdayIdStore extends IdStoreBase
{
    public $apiUrl = null;
    public $username = null;
    public $password = null;
    
    public $timeout = 45; // Timeout in seconds (per call to ID Store API).
    
    protected $httpClient = null;
    
    public function init()
    {
        $requiredProperties = [
            'apiUrl',
            'username',
            'password',
        ];
        foreach ($requiredProperties as $requiredProperty) {
            if (empty($this->$requiredProperty)) {
                throw new InvalidArgumentException(sprintf(
                    'No %s was provided.',
                    $requiredProperty
                ), 1532982562);
            }
        }
        
        parent::init();
    }
    
    public static function getIdBrokerFieldNames()
    {
        return [
            'Employee_Number' => User::EMPLOYEE_ID,
            'First_Name' => User::FIRST_NAME,
            'Last_Name' => User::LAST_NAME,
            'Display_Name' => User::DISPLAY_NAME,
            'Email' => User::EMAIL,
            'Username' => User::USERNAME,
            'Account_Locked__Disabled_or_Expired' => User::LOCKED,
            'requireMfa' => User::REQUIRE_MFA,
            'Manager_Email' => User::MANAGER_EMAIL,
            'Personal_Email' => User::PERSONAL_EMAIL,
            'Groups' => User::GROUPS,
            // No 'active' needed, since all ID Store records returned are active.
        ];
    }
    
    /**
     * Get the specified user's information. Note that inactive users will be
     * treated as non-existent users.
     *
     * @param string $employeeId The Employee ID.
     * @return User|null Information about the specified user, or null if no
     *     such active user was found.
     * @throws Exception
     */
    public function getActiveUser(string $employeeId)
    {
        throw new Exception(__FUNCTION__ . ' not yet implemented');
    }
    
    /**
     * Get a list of users' information (containing at least an Employee ID) for
     * all users changed since the specified time.
     *
     * @param int $unixTimestamp The time (as a UNIX timestamp).
     * @return User[]
     * @throws Exception
     */
    public function getUsersChangedSince(int $unixTimestamp)
    {
        throw new Exception(__FUNCTION__ . ' not yet implemented');
    }
    
    /**
     * Get information about each of the (active) users.
     *
     * @return User[] A list of Users.
     * @throws Exception
     */
    public function getAllActiveUsers()
    {
        $response = $this->getHttpClient()->get($this->apiUrl, [
            'auth' => [$this->username, $this->password, 'basic'],
            'connect_timeout' => $this->timeout,
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip',
            ],
            'http_errors' => false,
        ]);
        
        $statusCode = (int)$response->getStatusCode();
        if ($statusCode === 404) {
            $allActiveUsers = null;
        } elseif (($statusCode >= 200) && ($statusCode <= 299)) {
            $data = Json::decode($response->getBody());
            $allActiveUsers = $data['Report_Entry'] ?? null;
        } else {
            throw new Exception(sprintf(
                'Unexpected response (%s %s): %s',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $response->getBody()
            ), 1533069498);
        }
        
        if (! is_array($allActiveUsers)) {
            throw new Exception(sprintf(
                'Unexpected result when getting all active users: %s',
                var_export($allActiveUsers, true)
            ), 1532982679);
        }

        foreach ($allActiveUsers as $key => $user) {
            $company_ids = str_replace(" ", ",", $user["company_ids"] ?? "");
            $ou_tree = str_replace(" ", ",", $user["ou_tree"] ?? "");
            if (strlen($company_ids) > 0 && strlen($ou_tree) > 0) {
                $groups = $company_ids . "," . $ou_tree;
            } else {
                $groups = $company_ids . $ou_tree;
            }
            $allActiveUsers[$key]['Groups'] = $groups;
        }

        return self::getAsUsers($allActiveUsers);
    }
    
    /**
     * Get the HTTP client to use.
     *
     * @return Client
     */
    protected function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client();
        }
        return $this->httpClient;
    }
    
    public function getIdStoreName(): string
    {
        return 'Workday';
    }
}
