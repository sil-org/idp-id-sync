<?php
namespace Sil\Idp\IdSync\common\sync;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Sil\Idp\IdSync\common\models\User;
use yii\helpers\ArrayHelper;

class Synchronizer
{
    /** @var IdBrokerInterface */
    private $idBroker;
    
    /** @var IdStoreInterface */
    private $idStore;
    
    /** @var LoggerInterface */
    private $logger;
    
    public function __construct(
        IdStoreInterface $idStore,
        IdBrokerInterface $idBroker,
        LoggerInterface $logger = null
    ) {
        $this->idStore = $idStore;
        $this->idBroker = $idBroker;
        $this->logger = $logger ?? new NullLogger();
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
        $idBrokerUserInfoByEmployeeId = $this->getAllIdBrokerUsersByEmployeeId([
            'employee_id',
            'active',
        ]);
        
        $usersToAdd = [];
        $usersToUpdateAndActivate = [];
        $employeeIdsToDeactivate = [];
        
        foreach ($idStoreUsers as $idStoreUser) {
            $employeeId = $idStoreUser->employeeId;
            
            if (empty($employeeId)) {
                $this->logger->warning(sprintf(
                    'Received an empty Employee ID (%s) in the list of users '
                    . 'from the ID Store. Skipping it.',
                    var_export($employeeId, true)
                ));
                continue;
            }
            
            if (array_key_exists($employeeId, $idBrokerUserInfoByEmployeeId)) {
                // User exists in both places. Update and set as active:
                $usersToUpdateAndActivate[] = $idStoreUser;
            } else {
                // User is only in the ID Store. Add to ID Broker:
                $usersToAdd[] = $idStoreUser;
            }
            
            // Remove that user from the list of ID Broker users who have not
            // yet been processed.
            unset($idBrokerUserInfoByEmployeeId[$employeeId]);
        }
        
        // Deactivate the remaining (unprocessed) users in the ID Broker list.
        foreach ($idBrokerUserInfoByEmployeeId as $employeeId => $userInfo) {
            // If this user not currently inactive, deactivate them.
            if ($userInfo['active'] !== 'no') {
                $employeeIdsToDeactivate[] = $employeeId;
            }
        }
        
        /** @todo Add a safety check here to avoid deactivating too many users. */
        
        foreach ($usersToAdd as $userToAdd) {
            try {
                $this->idBroker->createUser($userToAdd->toArray());
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to add user to ID Broker (Employee ID: %s). '
                    . 'Error %s: %s',
                    var_export($userToAdd->employeeId, true),
                    $e->getCode(),
                    $e->getMessage()
                ));
            }
        }
        
        foreach ($usersToUpdateAndActivate as $userToUpdateAndActivate) {
            try {
                $this->activateAndUpdateUser($userToUpdateAndActivate);
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to update/activate user in the ID Broker (Employee ID: %s). '
                    . 'Error %s: %s',
                    var_export($userToUpdateAndActivate->employeeId, true),
                    $e->getCode(),
                    $e->getMessage()
                ));
            }
        }
        
        foreach ($employeeIdsToDeactivate as $employeeIdToDeactivate) {
            try {
                $this->deactivateUser($employeeIdToDeactivate);
            } catch (Exception $e) {
                $this->logger->error(sprintf(
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
                $this->logger->warning(sprintf(
                    'The list of users to sync included an empty Employee ID '
                    . '(%s). Skipping it.',
                    var_export($employeeId, true)
                ));
                continue;
            }
            
            try {
                $this->syncUser($employeeId);
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to sync one of the specified users (Employee ID: '
                    . '%s). Error (%s): %s',
                    var_export($employeeId, true),
                    $e->getCode(),
                    $e->getMessage()
                ));
            }
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
