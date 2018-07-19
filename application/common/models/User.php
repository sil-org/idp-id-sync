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
    const MANAGER_EMAIL = 'manager_email';
    const REQUIRE_MFA = 'require_mfa';
    const SPOUSE_EMAIL = 'spouse_email';
    const USERNAME = 'username';
    
    /** @var string */
    private $employeeId;
    
    /** @var string|null */
    private $firstName;
    
    /** @var string|null */
    private $lastName;
    
    /** @var string|null */
    private $displayName;
    
    /** @var string|null */
    private $username;
    
    /** @var string|null */
    private $email;
    
    /** @var string|null */
    private $active;
    
    /** @var string|null */
    private $locked;
    
    /** @var string|null */
    private $managerEmail;
    
    /** @var string|null */
    private $requireMfa;
    
    /** @var string|null */
    private $spouseEmail;
    
    /**
     * Create a new User model from the given user info, which must be an
     * associative array with keys matching this class's constants and which
     * must contain at least an `employee_id`.
     *
     * @param array $userInfo The user info for populating this User object.
     */
    public function __construct($userInfo = [])
    {
        if (empty($userInfo[self::EMPLOYEE_ID])) {
            throw new InvalidArgumentException('Employee ID cannot be empty.', 1493733219);
        }
        
        $this->employeeId = (string)$userInfo[self::EMPLOYEE_ID];
        $this->firstName = $userInfo[self::FIRST_NAME] ?? null;
        $this->lastName = $userInfo[self::LAST_NAME] ?? null;
        $this->displayName = $userInfo[self::DISPLAY_NAME] ?? null;
        $this->username = $userInfo[self::USERNAME] ?? null;
        $this->email = $userInfo[self::EMAIL] ?? null;
        $this->active = $userInfo[self::ACTIVE] ?? null;
        $this->setLocked($userInfo[self::LOCKED] ?? null);
        $this->managerEmail = $userInfo[self::MANAGER_EMAIL] ?? null;
        $this->setRequireMfa($userInfo[self::REQUIRE_MFA] ?? null);
        $this->spouseEmail = $userInfo[self::SPOUSE_EMAIL] ?? null;
    }
    
    /**
     * @return string
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }
    
    /**
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }
    
    /**
     * @return null|string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
    
    /**
     * @return null|string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
    
    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * @return null|string
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * @return null|string
     */
    public function getLocked()
    {
        return $this->locked;
    }
    
    /**
     * @return null|string
     */
    public function getManagerEmail()
    {
        return $this->managerEmail;
    }
    
    /**
     * @return null|string
     */
    public function getRequireMfa()
    {
        return $this->requireMfa;
    }
    
    /**
     * @return null|string
     */
    public function getSpouseEmail()
    {
        return $this->spouseEmail;
    }
    
    public function __toString()
    {
        return \json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
    
    protected function isAffirmative($value)
    {
        if ($value === null) {
            return false;
        } elseif (is_bool($value)) {
            return $value;
        }
        
        $lowercasedValue = strtolower(trim($value));
        
        return in_array($lowercasedValue, ['true', 'yes'], true);
    }
    
    public function setActive(string $active)
    {
        $this->active = $active;
    }
    
    public function setLocked($input)
    {
        if ($input === null) {
            return;
        }
        
        $this->locked = $this->isAffirmative($input) ? 'yes' : 'no';
    }
    
    public function setRequireMfa($input)
    {
        if ($input === null) {
            return;
        }
        
        $this->requireMfa = $this->isAffirmative($input) ? 'yes' : 'no';
    }
    
    /**
     * Get this User object's data as an associative array.
     *
     * NOTE: Only fields with non-null values will be included in the array.
     *
     * @return array
     */
    public function toArray()
    {
        $userInfo = [];
        $userInfo[self::EMPLOYEE_ID] = $this->employeeId;
        
        $possibleFields = [
            self::FIRST_NAME => $this->firstName,
            self::LAST_NAME => $this->lastName,
            self::DISPLAY_NAME => $this->displayName,
            self::USERNAME => $this->username,
            self::EMAIL => $this->email,
            self::ACTIVE => $this->active,
            self::LOCKED => $this->locked,
            self::MANAGER_EMAIL => $this->managerEmail,
            self::REQUIRE_MFA => $this->requireMfa,
            self::SPOUSE_EMAIL => $this->spouseEmail,
        ];

        foreach ($possibleFields as $fieldName => $value) {
            if ($value !== null) {
                $userInfo[$fieldName] = $value;
            }
        }
        
        return $userInfo;
    }
}
