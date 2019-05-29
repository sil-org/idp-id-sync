<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use CalcApiSig\HmacSigner;
use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class InsiteIdStore extends IdStoreBase
{
    public $apiKey = null;
    public $apiSecret = null;
    public $baseUrl = null;
    public $timeout = 30; // Timeout in seconds (per call to ID Store API).
    
    protected $httpClient = null;
    
    public function init()
    {
        $requiredProperties = [
            'apiKey',
            'apiSecret',
            'baseUrl',
        ];
        foreach ($requiredProperties as $requiredProperty) {
            if (empty($this->$requiredProperty)) {
                throw new InvalidArgumentException(sprintf(
                    'No %s was provided.',
                    $requiredProperty
                ), 1492115257);
            }
        }
        
        $this->baseUrl = rtrim($this->baseUrl, '/');
        
        parent::init();
    }
    
    public static function getIdBrokerFieldNames()
    {
        return [
            'employeenumber' => User::EMPLOYEE_ID,
            'firstname' => User::FIRST_NAME,
            'lastname' => User::LAST_NAME,
            'displayname' => User::DISPLAY_NAME,
            'email' => User::EMAIL,
            'username' => User::USERNAME,
            'locked' => User::LOCKED,
            'requires2sv' => User::REQUIRE_MFA,
            'supervisoremail' => User::MANAGER_EMAIL,
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
        $items = $this->getFromIdStore('/individual/' . $employeeId);
        $numItems = count($items);
        if ($numItems < 1) {
            return null;
        } elseif ($numItems === 1) {
            return self::getAsUser($items[0]);
        } else {
            throw new Exception(sprintf(
                'Too many results (%s) for Employee ID %s.',
                $numItems,
                var_export($employeeId, true)
            ), 1492443050);
        }
    }
    
    /**
     * Get a list of users' information (containing at least an Employee ID) for
     * all users changed since the specified time.
     *
     * @param int $unixTimestamp The time (as a UNIX timestamp).
     * @return User[]
     */
    public function getUsersChangedSince(int $unixTimestamp): array
    {
        $result = $this->getFromIdStore('/changes/' . $unixTimestamp);
        if (! is_array($result)) {
            throw new Exception(sprintf(
                'Unexpected result when getting users changed since %s (%s): %s',
                var_export($unixTimestamp, true),
                date('r', $unixTimestamp),
                var_export($result, true)
            ), 1492443064);
        }
        return self::getAsUsers($result);
    }
    
    public function getAllActiveUsers(): array
    {
        $result = $this->getFromIdStore('/all/');
        if (! is_array($result)) {
            throw new Exception(sprintf(
                'Unexpected result when getting all active users: %s',
                var_export($result, true)
            ), 1492444030);
        }
        return self::getAsUsers($result);
    }
    
    /**
     * Call the ID Store API itself.
     *
     * @param string $relativePath The URL to call, relative to the `baseUrl`.
     * @param array $queryParameters (Optional:) An array with key => value
     *     pairs that should be included as query string parameters. The
     *     `api_key` and `api_sig` will be added automatically.
     * @return array|null The resulting data, or null if unavailable (such as
     *     with a 404 response, or if no items were returned).
     * @throws Exception
     */
    protected function getFromIdStore(
        string $relativePath,
        array $queryParameters = []
    ) {
        $fullUrl = $this->baseUrl . $relativePath;
        $response = $this->getHttpClient()->get($fullUrl, [
            'connect_timeout' => $this->timeout,
            'headers' => [
                'Accept' => 'application/json', /** @todo Do we need/want this? */
                'Accept-Encoding' => 'gzip', /** @todo Do we need/want this? */
            ],
            'http_errors' => false,
            'query' => ArrayHelper::merge($queryParameters, [
                'api_key' => $this->apiKey,
                'api_sig' => HmacSigner::CalcApiSig(
                    $this->apiKey,
                    $this->apiSecret
                ),
            ]),
        ]);
        
        $statusCode = (int)$response->getStatusCode();
        if ($statusCode === 404) {
            return null;
        } elseif (($statusCode >= 200) && ($statusCode <= 299)) {
            $data = Json::decode($response->getBody());
            
            /**
             * @todo Detect paged results, and if present get the rest.
             */
            
            return $data['items'] ?? null;
        } else {
            throw new Exception(sprintf(
                'Unexpected response (%s %s): %s',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $response->getBody()
            ), 1492113596);
        }
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
        return 'Insite';
    }
}
