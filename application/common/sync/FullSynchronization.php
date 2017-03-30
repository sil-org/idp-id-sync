<?php
namespace Sil\Idp\IdSync\common\sync;

use Exception;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use yii\helpers\ArrayHelper;

class FullSynchronization
{
    private $idBroker;
    private $idStore;
    
    public function __construct(
        IdStoreInterface $idStore,
        IdBrokerInterface $idBroker
    ) {
        $this->idStore = $idStore;
        $this->idBroker = $idBroker;
    }
    
    protected function getIdBrokerUsersByEmployeeId()
    {
        $rawList = $this->idBroker->listUsers();
        $usersByEmployeeId = [];
        
        foreach ($rawList as $user) {
            $employeeId = $user['employee_id'];
            
            // Prevent duplicates.
            if (array_key_exists($employeeId, $usersByEmployeeId)) {
                throw new Exception(sprintf(
                    'Duplicate Employee ID found: %s',
                    $employeeId
                ), 1490801282);
            }
            
            unset($user['employee_id']);
            $usersByEmployeeId[$employeeId] = $user;
        }
        
        return $usersByEmployeeId;
    }
    
    public function run()
    {
        $idStoreUsers = $this->idStore->getAllActiveUsers();
        $idBrokerUsers = $this->getIdBrokerUsersByEmployeeId();
        
        foreach ($idStoreUsers as $idStoreUser) {
            $employeeId = $idStoreUser['employee_id'];
            
            if (array_key_exists($employeeId, $idBrokerUsers)) {
                // User exists in both places. Update and set as active:
                $this->idBroker->updateUser(
                    ArrayHelper::merge(['active' => 'yes'], $idStoreUser)
                );
            } else {
                // User is only in the ID Store. Add to ID Broker:
                $this->idBroker->createUser($idStoreUser);
            }
            
            // Remove that user from the list of ID Broker users who have not
            // yet been processed.
            unset($idBrokerUsers[$employeeId]);
        }
        
        // Deactivate the remaining (unprocessed) users in the ID Broker list.
        foreach (array_keys($idBrokerUsers) as $employeeId) {
            $this->idBroker->deactivateUser([
                'employee_id' => $employeeId,
                'active' => 'no',
            ]);
        }
    }
}
