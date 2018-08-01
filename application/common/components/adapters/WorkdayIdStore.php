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
            //'' => User::REQUIRE_MFA, /** @todo Insert correct field name */
            //'' => User::MANAGER_EMAIL, /** @todo Insert correct field name */
            'Spouse_Email_Work' => User::SPOUSE_EMAIL,
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
        throw new \Exception(__FUNCTION__ . ' not yet implemented');
    }
    
    /**
     * Get a list of users' information (containing at least an Employee ID) for
     * all users changed since the specified time.
     *
     * @param int $unixTimestamp The time (as a UNIX timestamp).
     * @return User[]
     * @throws Exception
     */
    public function getUsersChangedSince(int $unixTimestamp): array
    {
        throw new Exception(__FUNCTION__ . ' not yet implemented');
    }
    
    public function getAllActiveUsers(): array
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
            $allActiveUsers = $data['items'] ?? null;
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
        return self::getAsUsers($allActiveUsers);
    }
    
//    /**
//     * Call the ID Store API itself.
//     *
//     * @return array|null The resulting data, or null if unavailable (such as
//     *     with a 404 response, or if the list of results was not returned).
//     * @throws Exception
//     */
//    protected function getFromIdStore()
//    {
//    }
    
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
