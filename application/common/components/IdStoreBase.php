<?php
namespace Sil\Idp\IdSync\common\components;

use yii\base\Component;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;

abstract class IdStoreBase extends Component implements IdStoreInterface
{
    const ID_BROKER_DISPLAY_NAME = 'display_name';
    const ID_BROKER_EMAIL = 'email';
    const ID_BROKER_EMPLOYEE_ID = 'employee_id';
    const ID_BROKER_FIRST_NAME = 'first_name';
    const ID_BROKER_LAST_NAME = 'last_name';
    const ID_BROKER_LOCKED = 'locked';
    const ID_BROKER_USERNAME = 'username';
    
    /**
     * Get the list of ID Broker field names, indexed by the equivalent ID Store
     * field names.
     *
     * @var array<string,string>
     */
    abstract public static function getIdBrokerFieldNames();
    
    protected function getIdBrokerFieldNameFor(string $idStoreFieldName)
    {
        $idBrokerFieldNames = static::getIdBrokerFieldNames();
        return $idBrokerFieldNames[$idStoreFieldName];
    }
    
    /**
     * Take the given array of user info and translate the keys from the field
     * names used by the ID Store to those used by the ID Broker.
     *
     * @param array $userFromIdStore
     * @return array The array of user information, keyed on the ID Broker
     *     version of the field names.
     */
    protected function translateToIdBrokerFieldNames(array $userFromIdStore)
    {
        $userForIdBroker = [];
        
        foreach ($userFromIdStore as $idStoreFieldName => $value) {
            $idBrokerFieldName = $this->getIdBrokerFieldNameFor($idStoreFieldName);
            $userForIdBroker[$idBrokerFieldName] = $value;
        }
        
        return $userForIdBroker;
    }
}
