<?php
namespace Sil\Idp\IdSync\common\sync;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sil\Idp\IdSync\common\components\exceptions\MissingEmailException;
use Sil\Idp\IdSync\common\components\notify\NullNotifier;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\models\User;
use yii\helpers\ArrayHelper;

class Synchronizer
{
    public $dateTimeFormat = 'n/j/y g:ia T';
    
    /** @var IdBrokerInterface */
    private $idBroker;
    
    /** @var IdStoreInterface */
    private $idStore;
    
    /** @var LoggerInterface */
    private $logger;
    
    /** @var NotifierInterface */
    private $notifier;
    
    /**
     * Create a new Synchronizer object.
     *
     * @param IdStoreInterface $idStore The ID Store to communicate with.
     * @param IdBrokerInterface $idBroker The ID Broker to communicate with.
     * @param LoggerInterface|null $logger (Optional:) The PSR-3 logger to send
     *     log data to.
     * @param NotifierInterface|null $notifier (Optional:) An object for sending
     *     notifications.
     */
    public function __construct(
        IdStoreInterface $idStore,
        IdBrokerInterface $idBroker,
        LoggerInterface $logger = null,
        NotifierInterface $notifier = null
    ) {
        $this->idStore = $idStore;
        $this->idBroker = $idBroker;
        $this->logger = $logger ?? new NullLogger();
        $this->notifier = $notifier ?? new NullNotifier();
    }
    
    /**
     * Update the given user in the ID Broker, setting it to be active (unless
     * the given user already provides some other value for 'active').
     *
     * @param User $user The user's information.
     */
    protected function activateAndUpdateUser(User $user)
    {
        $this->idBroker->updateUser(
            ArrayHelper::merge(['active' => 'yes'], $user->toArray())
        );
        $this->logger->info('Updated/activated user: ' . $user->employeeId);
    }
    
    /**
     * Update the given Users in the ID Broker, setting them to be active
     * (unless the User already provides some other value for 'active').
     *
     * @param User[] $usersToUpdateAndActivate The user's information.
     */
    protected function activateAndUpdateUsers(array $usersToUpdateAndActivate)
    {
        $numUsersUpdated = 0;
        foreach ($usersToUpdateAndActivate as $userToUpdateAndActivate) {
            try {
                $this->activateAndUpdateUser($userToUpdateAndActivate);
                $numUsersUpdated += 1;
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to update/activate user in the ID Broker (Employee ID: %s). '
                    . 'Error %s: %s. [%s]',
                    var_export($userToUpdateAndActivate->employeeId, true),
                    $e->getCode(),
                    $e->getMessage(),
                    1494360119
                ));
            }
        }
        
        $this->logger->notice([
            'action' => 'update',
            'attempted' => count($usersToUpdateAndActivate),
            'succeeded' => $numUsersUpdated,
        ]);
    }
    
    /**
     * Create the given user in the ID Broker.
     *
     * NOTE: Make sure you catch (and handle) any `MissingEmailException`s
     *       thrown by this method.
     *
     * @param User $user The user's information.
     * @throws MissingEmailException
     */
    protected function createUser(User $user)
    {
        $this->idBroker->createUser($user->toArray());
        $this->logger->info('Created user: ' . $user->employeeId);
    }
    
    /**
     * Create the given Users in the ID Broker.
     *
     * @param User[] $usersToAdd The list of Users to be added.
     */
    protected function createUsers(array $usersToAdd)
    {
        $numUsersAdded = 0;
        $usersWithoutEmail = [];
        foreach ($usersToAdd as $userToAdd) {
            try {
                $this->createUser($userToAdd);
                $numUsersAdded += 1;
            } catch (MissingEmailException $e) {
                $this->logger->warning(sprintf(
                    'A User (Employee ID: %s) lacked an email address.',
                    $userToAdd->employeeId
                ));
                $usersWithoutEmail[] = $userToAdd;
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to add user to ID Broker (Employee ID: %s). '
                    . 'Error %s: %s. [%s]',
                    var_export($userToAdd->employeeId, true),
                    $e->getCode(),
                    $e->getMessage(),
                    1494360152
                ));
            }
        }
        
        $this->logger->notice([
            'action' => 'create',
            'attempted' => count($usersToAdd),
            'succeeded' => $numUsersAdded,
        ]);
        
        if ( ! empty($usersWithoutEmail)) {
            $this->notifier->sendMissingEmailNotice($usersWithoutEmail);
        }
    }
    
    public static function countActiveUsers($usersInfo, $activeFieldName)
    {
        return array_reduce($usersInfo, function($carry, $userInfo) use ($activeFieldName) {
            if (strcasecmp($userInfo[$activeFieldName], 'yes') === 0) {
                return $carry + 1;
            }
            return $carry;
        });
    }
    
    /**
     * Deactivate the specified user in the ID Broker.
     *
     * @param string $employeeId The Employee ID of the user to deactivate.
     */
    protected function deactivateUser($employeeId)
    {
        $this->idBroker->deactivateUser($employeeId);
        $this->logger->info('Deactivated user: ' . $employeeId);
    }
    
    /**
     * Deactivate the specified users in the ID Broker.
     *
     * @param string[] $employeeIdsToDeactivate The Employee IDs of the users to
     *     deactivate.
     */
    protected function deactivateUsers($employeeIdsToDeactivate)
    {
        $numUsersDeactivated = 0;
        foreach ($employeeIdsToDeactivate as $employeeIdToDeactivate) {
            try {
                $this->deactivateUser($employeeIdToDeactivate);
                $numUsersDeactivated += 1;
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to deactivate user in the ID Broker (Employee ID: %s). '
                    . 'Error %s: %s. [%s]',
                    var_export($employeeIdToDeactivate, true),
                    $e->getCode(),
                    $e->getMessage(),
                    1494360189
                ));
            }
        }
        
        $this->logger->notice([
            'action' => 'deactivate',
            'attempted' => count($employeeIdsToDeactivate),
            'succeeded' => $numUsersDeactivated,
        ]);
    }
    
    /**
     * Get a list of all users in the ID Broker, indexed by `employee_id`.
     *
     * @param array|null $fields (Optional:) The list of fields desired about
     *     each user in the result.
     * @return array<string,array>
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
                $this->logger->error(sprintf(
                    'Duplicate Employee ID found: %s. Skipping it. [%s]',
                    $employeeId,
                    1494360205
                ));
                continue;
            }
            
            $user->employeeId = null;
            $usersByEmployeeId[$employeeId] = $user->toArray();
        }
        
        return $usersByEmployeeId;
    }
    
    /**
     * Do a full synchronization, requesting all users from the ID Store and
     * updating all records in the ID Broker.
     *
     * @param int $maxDeactivationsPercent The percentage (0.0 - 1.0) of active
     *     users in ID Broker that it's okay to deactivate at a time. When used
     *     to calculate the actual number that can be deactivated during this
     *     run, the result is rounded up (so that we can always deactivate at
     *     least 1 user, assuming this value is > 0.0).
     * @throws Exception
     */
    public function syncAll($maxDeactivationsPercent = 0.15)
    {
        $this->logger->info('Syncing all users...');
        
        $idStoreUsers = $this->idStore->getAllActiveUsers();
        $idBrokerUserInfoByEmployeeId = $this->getAllIdBrokerUsersByEmployeeId([
            'employee_id',
            'active',
        ]);
        
        $numActiveUsersInBroker = self::countActiveUsers(
            $idBrokerUserInfoByEmployeeId,
            'active'
        );
        
        $usersToAdd = [];
        $usersToUpdateAndActivate = [];
        $employeeIdsToDeactivate = [];
        $employeeIdsAlreadyInactive = [];
        
        $this->logger->info(sprintf(
            'Found %s ID Store users and %s ID Broker users.',
            count($idStoreUsers),
            count($idBrokerUserInfoByEmployeeId)
        ));
        
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
            } else {
                $employeeIdsAlreadyInactive[] = $employeeId;
            }
        }
        
        $deactivationSafetyThreshold = ceil($numActiveUsersInBroker * $maxDeactivationsPercent);
        if (count($employeeIdsToDeactivate) > $deactivationSafetyThreshold) {
            $errorMessage = sprintf(
                'This sync was aborted because it would have deactivated %s of '
                . 'the %s active users found in ID Broker, which is above our '
                . 'safety threshold of %s (%s%%).',
                count($employeeIdsToDeactivate),
                $numActiveUsersInBroker,
                $deactivationSafetyThreshold,
                ($maxDeactivationsPercent * 100)
            );
            $this->logger->error($errorMessage);
            throw new Exception($errorMessage, 1499971625);
        }
        
        $this->createUsers($usersToAdd);
        $this->activateAndUpdateUsers($usersToUpdateAndActivate);
        $this->deactivateUsers($employeeIdsToDeactivate);
        
        $this->logger->notice([
            'action' => 'none (already inactive)',
            'count' => count($employeeIdsAlreadyInactive),
        ]);
        
        $this->logger->info('Done attempting to sync all users.');
    }
    
    /**
     * Synchronize a specific user, requesting their information from the
     * ID Store and updating it accordingly in the ID Broker.
     *
     * @param string $employeeId The Employee ID of the user to sync.
     */
    public function syncUser($employeeId)
    {
        try {
            $this->syncUserInternal($employeeId);
        } catch (MissingEmailException $e) {
            $this->logger->warning(sprintf(
                'That User (Employee ID: %s) lacked an email address.',
                $employeeId
            ));
            $user = new User([User::EMPLOYEE_ID => $employeeId]);
            $this->notifier->sendMissingEmailNotice([$user]);
        } catch (Exception $e) {
            $this->logger->error(sprintf(
                'Failed to sync the specified user (Employee ID: '
                . '%s). Error (%s): %s. [%s]',
                var_export($employeeId, true),
                $e->getCode(),
                $e->getMessage(),
                1495036251
            ));
        }
    }
    
    /**
     * Actually make the calls to synchronize the specified user.
     *
     * @param string $employeeId The Employee ID of the user to sync.
     */
    protected function syncUserInternal($employeeId)
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
                $this->createUser($idStoreUser);
            }
        } else {
            if ($isInIdBroker) {
                $this->deactivateUser($idBrokerUser->employeeId);
            } else {
                $this->logger->error(sprintf(
                    'Cannot find user anywhere: %s. [%s]',
                    $employeeId,
                    1494360236
                ));
            }
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
        $this->logger->info(sprintf(
            'Syncing %s specific users...',
            count($employeeIds)
        ));
        
        $usersWithoutEmail = [];
        
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
                $this->syncUserInternal($employeeId);
            } catch (MissingEmailException $e) {
                $this->logger->warning(sprintf(
                    'A User (Employee ID: %s) lacked an email address.',
                    $employeeId
                ));
                $usersWithoutEmail[] = new User([
                    User::EMPLOYEE_ID => $employeeId,
                ]);
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to sync one of the specified users (Employee ID: '
                    . '%s). Error (%s): %s. [%s]',
                    var_export($employeeId, true),
                    $e->getCode(),
                    $e->getMessage(),
                    1494360265
                ));
            }
        }
        
        if ( ! empty($usersWithoutEmail)) {
            $this->notifier->sendMissingEmailNotice($usersWithoutEmail);
        }
        
        $this->logger->info(sprintf(
            'Done attempting to sync %s specific users.',
            count($employeeIds)
        ));
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
        $this->logger->info(sprintf(
            'Syncing users changed since %s...',
            date($this->dateTimeFormat, $timestamp)
        ));
        
        $changedUsers = $this->idStore->getUsersChangedSince($timestamp);
        $employeeIds = [];
        foreach ($changedUsers as $changedUser) {
            $employeeIds[] = $changedUser->employeeId;
        }
        $this->syncUsers($employeeIds);
        
        $this->logger->info(sprintf(
            'Done attempting to sync users changed since %s.',
            date($this->dateTimeFormat, $timestamp)
        ));
    }
}
