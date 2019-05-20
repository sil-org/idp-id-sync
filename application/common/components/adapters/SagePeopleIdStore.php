<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\models\User;
use yii\helpers\Json;

class SagePeopleIdStore extends IdStoreBase
{
    public $authUrl = null;
    public $queryUrl = null;
    public $clientId = null;
    public $clientSecret = null;
    public $username = null;
    public $password = null;

    public $timeout = 45; // Timeout in seconds (per call to ID Store API).

    protected $httpClient = null;

    public function init()
    {
        $requiredProperties = [
            'authUrl',
            'queryUrl',
            'clientId',
            'clientSecret',
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

    /**
     * Return an associative array with key being the Sage People field names
     * and values being the ID Broker field names.
     * @return array
     */
    public static function getIdBrokerFieldNames()
    {
        return [
            'fHCM2__Team_Member__c.fHCM2__Unique_Id__c' => User::EMPLOYEE_ID,
            'fHCM2__Team_Member__c.fHCM2__First_Name__c' => User::FIRST_NAME,
            'fHCM2__Team_Member__c.fHCM2__Surname__c' => User::LAST_NAME,
            'fHCM2__Team_Member__c.Name' => User::DISPLAY_NAME,
            'User.Email' => User::EMAIL,
            'User.Username' => User::USERNAME,
    /** TODO: Inquire about the following two fields:
            'Account_Locked__Disabled_or_Expired' => User::LOCKED,
            'requireMfa' => User::REQUIRE_MFA,
    */
            'User.Manager.Email' => User::MANAGER_EMAIL,
            'fHCM2__Team_Member__c.fHCM2__Home_Email__c' => User::PERSONAL_EMAIL,
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
     * Get an access token by OAUTH request.
     *
     * @return string access token
     * @throws Exception
     */
    private function getAccessToken(): string
    {
        $response = $this->getHttpClient()->post($this->authUrl, [
            'connect_timeout' => $this->timeout,
            'http_errors' => false,
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'username' => $this->username,
                'password' => $this->password,
            ],
        ]);

        $statusCode = (int)$response->getStatusCode();
        if (($statusCode >= 200) && ($statusCode <= 299)) {
            $data = Json::decode($response->getBody());
            $accessToken = $data['access_token'] ?? '';
        } else {
            throw new Exception(sprintf(
                'Unexpected response (%s %s): %s',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $response->getBody()
            ), 1558380643);
        }

        return $accessToken;
    }

    /**
     * Get information about each of the (active) users.
     *
     * @return User[] A list of Users.
     * @throws Exception
     */
    public function getAllActiveUsers()
    {
        throw new Exception(__FUNCTION__ . ' not yet implemented');
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
        return 'Sage People';
    }
}
