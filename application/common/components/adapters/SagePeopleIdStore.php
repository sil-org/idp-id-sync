<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\models\User;
use Sil\PhpArrayDotNotation\DotNotation;
use yii\helpers\Json;

class SagePeopleIdStore extends IdStoreBase
{
    const PROP_EMPLOYEE_ID = 'fHCM2__Unique_Id__c';
    const PROP_FIRST_NAME = 'fHCM2__First_Name__c';
    const PROP_LAST_NAME = 'fHCM2__Surname__c';
    const PROP_DISPLAY_NAME = 'Name';
    const PROP_PERSONAL_EMAIL = 'fHCM2__Home_Email__c';
    const PROP_EMAIL = 'fHCM2__User__r.Email';
    const PROP_USERNAME = 'username';
    const PROP_MANAGER_EMAIL = 'fHCM2__Manager_User__r.Email';
    const PROP_LOCKED = 'fHCM2__User__r.jaars_Locked_From_IdP__c';
    const PROP_REQUIRE_MFA = 'fHCM2__User__r.jaars_Require_2sv_with_IdP__c';

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
                ), 1559070540);
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
            self::PROP_EMPLOYEE_ID => User::EMPLOYEE_ID,
            self::PROP_FIRST_NAME => User::FIRST_NAME,
            self::PROP_LAST_NAME => User::LAST_NAME,
            self::PROP_DISPLAY_NAME => User::DISPLAY_NAME,
            self::PROP_PERSONAL_EMAIL => User::PERSONAL_EMAIL,
            self::PROP_EMAIL => User::EMAIL,
            self::PROP_USERNAME => User::USERNAME,
            self::PROP_MANAGER_EMAIL => User::MANAGER_EMAIL,
            self::PROP_LOCKED => User::LOCKED,
            self::PROP_REQUIRE_MFA => User::REQUIRE_MFA,
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
        $safeEmployeeId = preg_replace('/[^\w]/', '', $employeeId);
        if ($employeeId !== $safeEmployeeId) {
            throw new \Exception('employee_id contains invalid characters');
        }
        $activeUsers = $this->getFromIdStore(sprintf(
            "WHERE %s='%s' AND fHCM2__Employment_Status__c='Active'",
            self::PROP_EMPLOYEE_ID,
            $safeEmployeeId
        ));
        $numItems = count($activeUsers);
        if ($numItems < 1) {
            return null;
        } elseif ($numItems === 1) {
            return self::getAsUser($activeUsers[0]);
        } else {
            throw new Exception(sprintf(
                'Too many results (%s) for Employee ID %s.',
                $numItems,
                var_export($employeeId, true)
            ), 1558533849);
        }
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
     * Parse response body into a one-dimensional associative array of properties.
     * @param array $body
     * @return array
     */
    private function getRecordsFromResponseBody($body)
    {
        if ($body['done'] !== true) {
            /*
             * This flag will be false if the amount of data is too large to be returned by one
             * query. At present, the maximum number of records returned in one response is 2000.
             * https://developer.salesforce.com/docs/atlas.en-us.soql_sosl.meta/soql_sosl/sforce_api_calls_soql_changing_batch_size.htm#!
             * If the data set grows beyond this boundary, this adapter will need to be revised
             * to support multi-batch queries.
             * (https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_query.htm)
             */
            \Yii::error($this->getIdStoreName() . ' responded with incomplete data set');
        }

        return array_map(
            function ($item) {
                $properties = DotNotation::collapse($item);

                /*
                 * Drop any non-word characters and replace spaces with underscores.
                 */
                $username = preg_replace(['/[\W]/', '/ /'], ['', '_'], $properties[self::PROP_DISPLAY_NAME]);
                $properties[self::PROP_USERNAME] = $username;

                return $properties;
            },
            $body['records'] ?? []
        );
    }

    /**
     * @param string $whereClause
     * @return array
     * @throws Exception
     */
    protected function getFromIdStore($whereClause): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->getHttpClient()->get($this->queryUrl, [
            'connect_timeout' => $this->timeout,
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip',
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'http_errors' => false,
            'query' => [
                'q' => 'SELECT '
                    . self::PROP_DISPLAY_NAME . ','
                    . self::PROP_FIRST_NAME . ','
                    . self::PROP_LAST_NAME . ','
                    . self::PROP_EMAIL . ','
                    . self::PROP_MANAGER_EMAIL . ','
                    . self::PROP_EMPLOYEE_ID . ','
                    . self::PROP_PERSONAL_EMAIL . ','
                    . self::PROP_LOCKED . ','
                    . self::PROP_REQUIRE_MFA
                    . ' FROM fHCM2__Team_Member__c '
                    . $whereClause
                    . ' AND fHCM2__User__c!=null',
            ],
        ]);

        $statusCode = (int)$response->getStatusCode();

        if (($statusCode >= 400) && ($statusCode <= 499)) {
            $body = null;
        } elseif (($statusCode >= 200) && ($statusCode <= 299)) {
            $body = Json::decode($response->getBody());
        } else {
            throw new Exception(sprintf(
                'Unexpected response (%s %s): %s',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $response->getBody()
            ), 1558380644);
        }

        if (! is_array($body)) {
            throw new Exception(sprintf(
                'Unexpected result when getting all active users: %s',
                var_export($body, true)
            ), 1558380645);
        }

        $activeUsers = self::getRecordsFromResponseBody($body);
        return $activeUsers;
    }

    /**
     * Get information about each of the (active) users.
     *
     * @return User[] A list of Users.
     * @throws Exception
     */
    public function getAllActiveUsers()
    {
        $allActiveUsers = $this->getFromIdStore(
            "WHERE fHCM2__Employment_Status__c='Active'"
        );

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
        return 'Sage People';
    }
}
