<?php
namespace Sil\Idp\IdSync\common\components;

use Sil\Idp\IdSync\common\components\adapters\fakes\FakeIdBroker;
use Sil\Idp\IdSync\common\components\adapters\IdpIdBroker;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\models\User;
use yii\base\Component;

abstract class IdBrokerBase extends Component implements IdBrokerInterface
{
    const ADAPTER_FAKE = 'fake';
    const ADAPTER_IDP = 'idp';
    
    public $accessToken;
    public $baseUrl;
    
    protected static $adapters = [
        self::ADAPTER_FAKE => FakeIdBroker::class,
        self::ADAPTER_IDP => IdpIdBroker::class,
    ];
    
    public static function getAdapterClassFor($adapterName)
    {
        if (array_key_exists($adapterName, self::$adapters)) {
            return self::$adapters[$adapterName];
        }
        
        throw new \InvalidArgumentException(sprintf(
            "Unknown ID Broker adapter (%s). Must be one of the following: \n%s\n",
            $adapterName,
            join("\n", array_keys(self::$adapters))
        ), 1491327756);
    }
    
    /**
     * Convert information about a list of users (each being an array of user
     * information keyed on ID Broker field names) into a list of User objects.
     *
     * @param array[] $idBrokerUserInfoList A list of users' info from ID Broker.
     * @return User[] A list of objects representing those users' info in a
     *     standard way.
     */
    protected static function getAsUsers($idBrokerUserInfoList)
    {
        return array_map(function($entry) {
            return new User($entry);
        }, $idBrokerUserInfoList);
    }
}
