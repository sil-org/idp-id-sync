<?php
namespace Sil\Idp\IdSync\common\components;

use Sil\Idp\IdSync\common\components\adapters\GoogleSheetsIdStore;
use Sil\Idp\IdSync\common\components\adapters\InsiteIdStore;
use Sil\Idp\IdSync\common\components\adapters\fakes\FakeIdStore;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Sil\Idp\IdSync\common\models\User;
use yii\base\Component;

abstract class IdStoreBase extends Component implements IdStoreInterface
{
    const ADAPTER_FAKE = 'fake';
    const ADAPTER_GOOGLE_SHEETS = 'googlesheets';
    const ADAPTER_INSITE = 'insite';
    
    protected static $adapters = [
        self::ADAPTER_FAKE => FakeIdStore::class,
        self::ADAPTER_GOOGLE_SHEETS => GoogleSheetsIdStore::class,
        self::ADAPTER_INSITE => InsiteIdStore::class,
    ];
    
    public static function getAdapterClassFor($adapterName)
    {
        if (array_key_exists($adapterName, self::$adapters)) {
            return self::$adapters[$adapterName];
        }
        
        throw new \InvalidArgumentException(sprintf(
            "Unknown ID Store adapter (%s). Must be one of the following: \n%s\n",
            $adapterName,
            join("\n", array_keys(self::$adapters))
        ), 1491316896);
    }
    
    /**
     * Convert user information keyed on ID Store field names into a User object.
     *
     * @param array $idStoreUserInfo User info from ID Store.
     * @return User An object representing that user info in a standard way.
     */
    protected static function getAsUser($idStoreUserInfo)
    {
        return new User(self::translateToIdBrokerFieldNames($idStoreUserInfo));
    }
    
    /**
     * Convert information about a list of users (each being an array of user
     * information keyed on ID Store field names) into a list of User objects.
     *
     * @param array[] $idStoreUserInfoList A list of users' info from ID Store.
     * @return User[] A list of objects representing those users' info in a
     *     standard way.
     */
    protected static function getAsUsers($idStoreUserInfoList)
    {
        return array_map(function($entry) {
            return self::getAsUser($entry);
        }, $idStoreUserInfoList);
    }
    
    /**
     * Get the list of ID Broker field names, indexed by the equivalent ID Store
     * field names.
     *
     * @var array<string,string>
     */
    abstract public static function getIdBrokerFieldNames();
    
    protected static function getIdBrokerFieldNameFor(string $idStoreFieldName)
    {
        $idBrokerFieldNames = static::getIdBrokerFieldNames();
        return $idBrokerFieldNames[$idStoreFieldName];
    }
    
    /**
     * Take the given user info and translate the keys from the field names used
     * by the ID Store to those used by the ID Broker.
     *
     * @param array $userFromIdStore
     * @return array The array of user information, keyed on the ID Broker
     *     version of the field names.
     */
    public static function translateToIdBrokerFieldNames(array $userFromIdStore)
    {
        $userForIdBroker = [];
        
        foreach ($userFromIdStore as $idStoreFieldName => $value) {
            $idBrokerFieldName = self::getIdBrokerFieldNameFor($idStoreFieldName);
            $userForIdBroker[$idBrokerFieldName] = $value;
        }
        
        return $userForIdBroker;
    }
}
