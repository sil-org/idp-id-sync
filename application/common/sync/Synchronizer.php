<?php
namespace Sil\Idp\IdSync\common\sync;

use Exception;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Sil\Idp\IdSync\common\models\User;
use Yii;
use yii\helpers\ArrayHelper;

class Synchronizer
{
    /** @var IdBrokerInterface */
    private $idBroker;
    
    /** @var IdStoreInterface */
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
     * @param User $user The user's information (as key/value pairs).
     */
    protected function activateAndUpdateUser(User $user)
    {
        $this->idBroker->updateUser(
            ArrayHelper::merge(['active' => 'yes'], $user->toArray())
        );
    }
    
    /**
     * Deactivate the specified user in the ID Broker.
     *
     * @param string $employeeId The Employee ID of the user to deactivate.
     */
    protected function deactivateUser($employeeId)
    {
        $this->idBroker->deactivateUser($employeeId);
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
            /* @var $user User */
            $employeeId = $user->employeeId;
            
            // Prevent duplicates.
            if (array_key_exists($employeeId, $usersByEmployeeId)) {
                throw new Exception(sprintf(
                    'Duplicate Employee ID found: %s',
                    $employeeId
                ), 1490801282);
            }
            
            $user->employeeId = null;
            $usersByEmployeeId[$employeeId] = $user->toArray();
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
        $idBrokerUserInfoByEmployeeId = $this->getAllIdBrokerUsersByEmployeeId();
        
        foreach ($idStoreUsers as $idStoreUser) {
            $employeeId = $idStoreUser->employeeId;
            
            if (empty($employeeId)) {
                Yii::warning(sprintf(
                    'Received an empty Employee ID (%s) in the list of users '
                    . 'from the ID Store. Skipping it.',
                    var_export($employeeId, true)
                ));
                continue;
            }
            
            if (array_key_exists($employeeId, $idBrokerUserInfoByEmployeeId)) {
                // User exists in both places. Update and set as active:
                $this->activateAndUpdateUser($idStoreUser);
            } else {
                // User is only in the ID Store. Add to ID Broker:
                $this->idBroker->createUser($idStoreUser->toArray());
            }
            
            // Remove that user from the list of ID Broker users who have not
            // yet been processed.
            unset($idBrokerUserInfoByEmployeeId[$employeeId]);
        }
        
        // Deactivate the remaining (unprocessed) users in the ID Broker list.
        foreach (array_keys($idBrokerUserInfoByEmployeeId) as $employeeId) {
            $this->deactivateUser($employeeId);
        }
    }
    
    /**
     * Synchronize a specific user, requesting their information from the
     * ID Store and updating it accordingly in the ID Broker.
     *
     * @param string $employeeId The EmployeeID of the user to sync.
     */
    public function syncUser($employeeId)
    {
        if (empty($employeeId)) {
            throw new \InvalidArgumentException(
                'Employee ID cannot be empty',
                1491336331
            );
        }
        
        $idStoreUser = $this->idStore->getActiveUser($employeeId);
        $idBrokerUser = $this->idBroker->getUser($employeeId);
        
        $isInIdStore = ($idStoreUser !== null);
        $isInIdBroker = ($idBrokerUser !== null);
        
        if ($isInIdStore) {
            if ($isInIdBroker) {
                $this->activateAndUpdateUser($idStoreUser);
            } else {
                $this->idBroker->createUser($idStoreUser->toArray());
            }
        } else {
            if ($isInIdBroker) {
                $this->deactivateUser($idBrokerUser->employeeId);
            } // else: Nothing to do, since the user doesn't exist anywhere.
        }
    }
    
    /**
     * Synchronize a specific set of users.
     *
     * @param string[] $employeeIds A list of Employee IDs indicating which
     *     users to sync.
     */
    public function syncUsers(array $employeeIds)
    {
        foreach ($employeeIds as $employeeId) {
            if (empty($employeeId)) {
                Yii::warning(sprintf(
                    'The list of users to sync included an empty Employee ID '
                    . '(%s). Skipping it.',
                    var_export($employeeId, true)
                ));
                continue;
            }
            
            $this->syncUser($employeeId);
        }
    }
    
    /**
     * Get the list of users that the ID Store believes have been changed
     * (added, altered, or removed/deactivated) since the given timestamp, then
     * synchronize those specific users with the ID Broker.
     *
     * @param int $timestamp The date/time, as a Unix timestamp.
     */
    public function syncUsersChangedSince($timestamp)
    {
        $changedUsers = $this->idStore->getUsersChangedSince($timestamp);
        $employeeIds = [];
        foreach ($changedUsers as $changedUser) {
            $employeeIds[] = $changedUser->employeeId;
        }
        $this->syncUsers($employeeIds);
    }
}
