<?php
namespace Sil\Idp\IdSync\common\models;

use InvalidArgumentException;

class User
{
    const ACTIVE = 'active';
    const DISPLAY_NAME = 'display_name';
    const EMAIL = 'email';
    const EMPLOYEE_ID = 'employee_id';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const LOCKED = 'locked';
    const USERNAME = 'username';
    
    /** @var string */
    public $employeeId;
    
    /** @var string|null */
    public $firstName;
    
    /** @var string|null */
    public $lastName;
    
    /** @var string|null */
    public $displayName;
    
    /** @var string|null */
    public $username;
    
    /** @var string|null */
    public $email;
    
    /** @var string|null */
    public $active;
    
    /** @var string|null */
    public $locked;
    
    /**
     * Create a new User model from the given user info, which must be an
     * associative array with keys matching this class's constants and which
     * must contain at least an `employee_id`.
     *
     * @param array $userInfo The user info for populating this User object.
     */
    public function __construct($userInfo = [])
    {
        $this->employeeId = $userInfo[self::EMPLOYEE_ID];
        $this->firstName = $userInfo[self::FIRST_NAME] ?? null;
        $this->lastName = $userInfo[self::LAST_NAME] ?? null;
        $this->displayName = $userInfo[self::DISPLAY_NAME] ?? null;
        $this->username = $userInfo[self::USERNAME] ?? null;
        $this->email = $userInfo[self::EMAIL] ?? null;
        $this->active = $userInfo[self::ACTIVE] ?? null;
        $this->locked = $userInfo[self::LOCKED] ?? null;
        
        if (empty($this->employeeId)) {
            throw new InvalidArgumentException('Employee ID cannot be empty.', 1493733219);
        }
    }
    
    public function toArray()
    {
        $userInfo = [];
        $userInfo[self::EMPLOYEE_ID] = $this->employeeId;
        if ($this->firstName !== null) {
            $userInfo[self::FIRST_NAME] = $this->firstName;
        }
        if ($this->lastName !== null) {
            $userInfo[self::LAST_NAME] = $this->lastName;
        }
        if ($this->displayName !== null) {
            $userInfo[self::DISPLAY_NAME] = $this->displayName;
        }
        if ($this->username !== null) {
            $userInfo[self::USERNAME] = $this->username;
        }
        if ($this->email !== null) {
            $userInfo[self::EMAIL] = $this->email;
        }
        if ($this->active !== null) {
            $userInfo[self::ACTIVE] = $this->active;
        }
        if ($this->locked !== null) {
            $userInfo[self::LOCKED] = $this->locked;
        }
        return $userInfo;
    }
}
