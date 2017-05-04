<?php
namespace Sil\Idp\IdSync\common\sync;

use Exception;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Yii;
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
     * @param array|null $fields (Optional:) The list of fields desired about
     *     each user in the result.
     * @return array<string,array>
     * @throws Exception
     */
    protected function getAllIdBrokerUsersByEmployeeId($fields = null)
    {
        $rawList = $this->idBroker->listUsers($fields);
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
        $idBrokerUsers = $this->getAllIdBrokerUsersByEmployeeId([
            'employee_id',
            'active',
        ]);
        
        $usersToAdd = [];
        $usersToUpdateAndActivate = [];
        $employeeIdsToDeactivate = [];
        
        foreach ($idStoreUsers as $idStoreUser) {
            $employeeId = $idStoreUser['employee_id'];
            
            if (empty($employeeId)) {
                Yii::warning(sprintf(
                    'Received an empty Employee ID (%s) in the list of users '
                    . 'from the ID Store. Skipping it.',
                    var_export($employeeId, true)
                ));
                continue;
            }
            
            if (array_key_exists($employeeId, $idBrokerUsers)) {
                // User exists in both places. Update and set as active:
                $usersToUpdateAndActivate[] = $idStoreUser;
            } else {
                // User is only in the ID Store. Add to ID Broker:
                $usersToAdd[] = $idStoreUser;
            }
            
            // Remove that user from the list of ID Broker users who have not
            // yet been processed.
            unset($idBrokerUsers[$employeeId]);
        }
        
        // Deactivate the remaining (unprocessed) users in the ID Broker list.
        foreach ($idBrokerUsers as $employeeId => $userInfo) {
            // If this user not currently inactive, deactivate them.
            if ($userInfo['active'] !== 'no') {
                $employeeIdsToDeactivate[] = $employeeId;
            }
        }
        
        /** @todo Add a safety check here to avoid deactivating too many users. */
        
        foreach ($usersToAdd as $userToAdd) {
            try {
                $this->idBroker->createUser($userToAdd);
            } catch (Exception $e) {
                Yii::error(sprintf(
                    'Failed to add user to ID Broker (Employee ID: %s). '
                    . 'Error %s: %s',
                    var_export($userToAdd['employee_id'] ?? null, true),
                    $e->getCode(),
                    $e->getMessage()
                ));
            }
        }
        
        foreach ($usersToUpdateAndActivate as $userToUpdateAndActivate) {
            try {
                $this->activateAndUpdateUser($userToUpdateAndActivate);
            } catch (Exception $e) {
                Yii::error(sprintf(
                    'Failed to update/activate user in the ID Broker (Employee ID: %s). '
                    . 'Error %s: %s',
                    var_export($userToUpdateAndActivate['employee_id'] ?? null, true),
                    $e->getCode(),
                    $e->getMessage()
                ));
            }
        }
        
        foreach ($employeeIdsToDeactivate as $employeeIdToDeactivate) {
            try {
                $this->deactivateUser($employeeIdToDeactivate);
            } catch (Exception $e) {
                Yii::error(sprintf(
                    'Failed to deactivate user in the ID Broker (Employee ID: %s). '
                    . 'Error %s: %s',
                    var_export($employeeIdToDeactivate, true),
                    $e->getCode(),
                    $e->getMessage()
                ));
            }
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
                $this->idBroker->createUser($idStoreUser);
            }
        } else {
            if ($isInIdBroker) {
                $this->deactivateUser($idBrokerUser['employee_id']);
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
            $employeeIds[] = $changedUser['employeenumber'];
        }
        $synchronizer = new Synchronizer($this->idStore, $this->idBroker);
        $synchronizer->syncUsers($employeeIds);
    }
}
