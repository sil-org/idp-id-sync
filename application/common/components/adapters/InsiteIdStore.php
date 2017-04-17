<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use CalcApiSig\HmacSigner;
use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Sil\Idp\IdSync\common\components\IdStoreBase;
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
        
        parent::init();
    }
    
    public static function getIdBrokerFieldNames()
    {
        return [
            'employeenumber' => self::ID_BROKER_EMPLOYEE_ID,
            'firstname' => self::ID_BROKER_FIRST_NAME,
            'lastname' => self::ID_BROKER_LAST_NAME,
            'displayname' => self::ID_BROKER_DISPLAY_NAME,
            'email' => self::ID_BROKER_EMAIL,
            'username' => self::ID_BROKER_USERNAME,
            'locked' => self::ID_BROKER_LOCKED,
            // No 'active' needed, since all ID Store records returned are active.
        ];
    }
    
    /**
     * Get the specified user's info, or null if no such active user was found.
     *
     * @param string $employeeId The Employee ID of the desired user.
     * @return array|null The user's information (or null if not found/active).
     * @throws Exception
     */
    public function getActiveUser(string $employeeId)
    {
        $items = $this->getFromIdStore('/individual/' . $employeeId);
        $numItems = count($items);
        if ($numItems < 1) {
            return null;
        } elseif ($numItems === 1) {
            return $items[0];
        } else {
            throw Exception(sprintf(
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
     * @return array
     */
    public function getUsersChangedSince(int $unixTimestamp): array
    {
        $result = $this->getFromIdStore('/change/' . $unixTimestamp);
        if ( ! is_array($result)) {
            throw new Exception(sprintf(
                'Unexpected result when getting users changed since %s (%s): %s',
                var_export($unixTimestamp, true),
                date('r', $unixTimestamp),
                var_export($result, true)
            ), 1492443064);
        }
        return $result;
    }
    
    public function getAllActiveUsers(): array
    {
        $result = $this->getFromIdStore('/all/');
        if ( ! is_array($result)) {
            throw new Exception(sprintf(
                'Unexpected result when getting all active users: %s',
                var_export($result, true)
            ), 1492444030);
        }
        return $result;
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
}
