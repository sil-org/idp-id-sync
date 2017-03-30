<?php
namespace Sil\Idp\IdSync\common\components;

use yii\base\Component;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;

abstract class IdStoreBase extends Component implements IdStoreInterface
{
    /**
     * ID Broker field names, indexed by equivalent ID Store field names.
     *
     * @var array<string,string>
     */
    protected $idBrokerFieldNames = [
        'employeeNumber' => 'employee_id',
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'displayName' => 'display_name',
        'email' => 'email',
        'username' => 'username',
        'locked' => 'locked',
    ];
    
    protected function getIdBrokerFieldName(string $idStoreFieldName)
    {
        return $this->idBrokerFieldNames[$idStoreFieldName];
    }
    
    protected function translateToIdBrokerFieldNames(array $userFromIdStore)
    {
        $userForIdBroker = [];
        
        foreach ($userFromIdStore as $idStoreFieldName => $value) {
            $idBrokerFieldName = $this->getIdBrokerFieldName($idStoreFieldName);
            $userForIdBroker[$idBrokerFieldName] = $value;
        }
        
        return $userForIdBroker;
    }
}
