<?php
namespace Sil\Idp\IdSync\common\sync;

use Exception;
use InvalidArgumentException;
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
    /** @var float */
    const MIN_NUM_CHANGES_ALLOWED = 10;
    const SAFETY_CUTOFF_DEFAULT = 0.15;
    
    public $dateTimeFormat = 'n/j/y g:ia T';
    
    /** @var IdBrokerInterface */
    private $idBroker;
    
    /** @var IdStoreInterface */
    private $idStore;
    
    /** @var LoggerInterface */
    private $logger;
    
    /** @var NotifierInterface */
    private $notifier;
    
    /** @var float */
    private $safetyCutoff;
    
    /**
     * Create a new Synchronizer object.
     *
     * @param IdStoreInterface $idStore The ID Store to communicate with.
     * @param IdBrokerInterface $idBroker The ID Broker to communicate with.
     * @param LoggerInterface|null $logger (Optional:) The PSR-3 logger to send
     *     log data to.
     * @param NotifierInterface|null $notifier (Optional:) An object for sending
     *     notifications.
     * @param float|null $safetyCutoff (Optional:) The cutoff for what fraction
     *     of the active users in ID Broker may be changed by a single
     *     operation (e.g. limiting how many accounts can be deactivated in a
     *     single syncAll() run). When used to calculate the actual number of
     *     changes that can be made for a given sync, the resulting value is
     *     rounded up (so that we can always make at least 1 change, assuming
     *     this value is > 0.0 and there are any active users in ID Broker). If
     *     no value is given, a default value will be used.
     */
    public function __construct(
        IdStoreInterface $idStore,
        IdBrokerInterface $idBroker,
        LoggerInterface $logger = null,
        NotifierInterface $notifier = null,
        $safetyCutoff = null
    ) {
        $this->idStore = $idStore;
        $this->idBroker = $idBroker;
        $this->logger = $logger ?? new NullLogger();
        $this->notifier = $notifier ?? new NullNotifier();
        $this->safetyCutoff = $safetyCutoff ?? self::SAFETY_CUTOFF_DEFAULT;
        
        $this->logger->info('Safety cutoff: {value}', [
            'value' => var_export($this->safetyCutoff, true),
        ]);
        
        $this->assertValidSafetyCutoff($this->safetyCutoff);
    }
    
    /**
     * Abort the sync (and log an error message) due to surpassing the safety
     * cutoff.
     *
     * @param string $changeType The type of change (e.g. 'create', 'update', or
     *     'deactivate').
     * @param int $numChanges The number of changes of that type that would have
     *     been attempted.
     * @param int $numChangesAllowed The number of changes allowed.
     * @param int $numActiveUsersInBroker The number of active users currently
     *     in ID Broker.
     * @param int $errorCode The error code.
     * @throws Exception
     */
    protected function abortSync(
        $changeType,
        $numChanges,
        $numChangesAllowed,
        $numActiveUsersInBroker,
        $errorCode
    ) {
        $errorMessage = sprintf(
            'This sync was aborted because it would have tried to %s %s users '
            . 'in ID Broker, which is above our safety threshold of %s '
            . '(%s%% of the %s active users currently in ID Broker).',
            $changeType,
            $numChanges,
            $numChangesAllowed,
            ($this->safetyCutoff * 100),
            $numActiveUsersInBroker
        );
        $this->logger->error($errorMessage);
        throw new Exception($errorMessage, $errorCode);
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
    
    protected function assertValidSafetyCutoff($value)
    {
        if (! self::isValidSafetyCutoff($value)) {
            $errorMessage = sprintf(
                'The safety cutoff must be a number from 0.0 to 1.0 (not %s).',
                var_export($value, true)
            );
            $this->logger->error($errorMessage);
            throw new InvalidArgumentException($errorMessage, 1500322937);
        }
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
        
        if (! empty($usersWithoutEmail)) {
            $this->notifier->sendMissingEmailNotice($usersWithoutEmail);
        }
    }
    
    public static function countActiveUsers($usersInfo, $activeFieldName = 'active')
    {
        return array_reduce($usersInfo, function ($carry, $userInfo) use ($activeFieldName) {
            if (strcasecmp($userInfo[$activeFieldName], 'yes') === 0) {
                return $carry + 1;
            }
            return $carry;
        }, 0);
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
        if (is_array($fields) && ! in_array(User::EMPLOYEE_ID, $fields)) {
            throw new InvalidArgumentException(sprintf(
                'The list of fields, if provided, must include %s. Given list: %s',
                User::EMPLOYEE_ID,
                join(', ', $fields)
            ), 1501181580);
        }
        
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
    
    protected function getNumActiveUsersInBroker($idBrokerUsersInfo = null)
    {
        if ($idBrokerUsersInfo === null) {
            $idBrokerUsersInfo = $this->getAllIdBrokerUsersByEmployeeId([
                'employee_id',
                'active',
            ]);
        }
        return self::countActiveUsers($idBrokerUsersInfo);
    }
    
    protected function getNumChangesAllowed($numActiveUsersInBroker)
    {
        return max(
            ceil($numActiveUsersInBroker * $this->safetyCutoff),
            self::MIN_NUM_CHANGES_ALLOWED
        );
    }
    
    public static function isValidSafetyCutoff($value)
    {
        return is_numeric($value) && ($value >= 0.0);
    }
    
    /**
     * Do a full synchronization, requesting all users from the ID Store and
     * updating all records in the ID Broker.
     *
     * @throws Exception
     */
    public function syncAll()
    {
        $this->logger->info('Syncing all users...');
        
        $idStoreUsers = $this->idStore->getAllActiveUsers();
        $idBrokerUserInfoByEmployeeId = $this->getAllIdBrokerUsersByEmployeeId([
            'employee_id',
            'active',
        ]);
        
        $numActiveUsersInBroker = $this->getNumActiveUsersInBroker(
            $idBrokerUserInfoByEmployeeId
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
        
        $numChangesAllowed = $this->getNumChangesAllowed($numActiveUsersInBroker);
        
        if (count($usersToAdd) > $numChangesAllowed) {
            $this->abortSync(
                'create',
                count($usersToAdd),
                $numChangesAllowed,
                $numActiveUsersInBroker,
                1501165932
            );
        }
        
        /**
         * NOTE: If we begin intelligently comparing users found in both places
         * and only updating those that need it, then we could limit updates to
         * the safety cutoff as well. For now, we try to update all users found
         * to exist in both places, so for a full sync that will almost always
         * be above the safety cutoff.
         */
        
        if (count($employeeIdsToDeactivate) > $numChangesAllowed) {
            $this->abortSync(
                'deactivate',
                count($employeeIdsToDeactivate),
                $numChangesAllowed,
                $numActiveUsersInBroker,
                1499971625
            );
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
        
        if (! empty($usersWithoutEmail)) {
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
        
        $numActiveUsersInBroker = $this->getNumActiveUsersInBroker();
        $numChangesAllowed = $this->getNumChangesAllowed($numActiveUsersInBroker);
        
        if (count($employeeIds) > $numChangesAllowed) {
            $this->abortSync(
                'change',
                count($employeeIds),
                $numChangesAllowed,
                $numActiveUsersInBroker,
                1501177946
            );
        }
        
        $this->syncUsers($employeeIds);
        
        $this->logger->info(sprintf(
            'Done attempting to sync users changed since %s.',
            date($this->dateTimeFormat, $timestamp)
        ));
    }
}
