<?php
namespace Sil\Idp\IdSync\common\sync;

use Exception;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use yii\helpers\ArrayHelper;

class Synchronizer
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
    
    /**
     * Update the given user in the ID Broker, setting it to be active (unless
     * the given user already provides some other value for 'active').
     *
     * @param array $user The user's information (as key/value pairs).
     */
    protected function activateAndUpdateUser($user)
    {
        $this->idBroker->updateUser(
            ArrayHelper::merge(['active' => 'yes'], $user)
        );
    }
    
    /**
     * Get a list of all users in the ID Broker, indexed by `employee_id`.
     *
     * @return array<string,array>
     * @throws Exception
     */
    protected function getAllIdBrokerUsersByEmployeeId()
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
    
    /**
     * Do a full synchronization, requesting all users from the ID Store and
     * updating all records in the ID Broker.
     */
    public function syncAll()
    {
        $idStoreUsers = $this->idStore->getAllActiveUsers();
        $idBrokerUsers = $this->getAllIdBrokerUsersByEmployeeId();
        
        foreach ($idStoreUsers as $idStoreUser) {
            $employeeId = $idStoreUser['employee_id'];
            
            if (array_key_exists($employeeId, $idBrokerUsers)) {
                // User exists in both places. Update and set as active:
                $this->activateAndUpdateUser($idStoreUser);
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
