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
    
    /**
     * The values (indexed by field name) for the fields which have been set.
     *
     * @var array<string,mixed>
     */
    private $values = [];
    
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
        
        // Set all of the provided fields, taking whatever value was given.
        foreach (self::getAllFieldNames() as $fieldName) {
            if (array_key_exists($fieldName, $userInfo)) {
                $this->values[$fieldName] = $userInfo[$fieldName];
            }
        }
        
        // Ensure fields with stricter constraints have valid values.
        $this->values[self::EMPLOYEE_ID] = (string)$userInfo[self::EMPLOYEE_ID];
        $this->setLocked($userInfo[self::LOCKED] ?? null);
        $this->setRequireMfa($userInfo[self::REQUIRE_MFA] ?? null);
    }
    
    /**
     * Get the list of all of the field names supported by this User model.
     *
     * @return string[]
     */
    public static function getAllFieldNames()
    {
        return [
            self::ACTIVE,
            self::DISPLAY_NAME,
            self::EMAIL,
            self::EMPLOYEE_ID,
            self::FIRST_NAME,
            self::LAST_NAME,
            self::LOCKED,
            self::MANAGER_EMAIL,
            self::REQUIRE_MFA,
            self::SPOUSE_EMAIL,
            self::USERNAME,
        ];
    }
    
    /**
     * @return string
     */
    public function getEmployeeId()
    {
        return $this->values[self::EMPLOYEE_ID];
    }
    
    /**
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->values[self::FIRST_NAME] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getLastName()
    {
        return $this->values[self::LAST_NAME] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getDisplayName()
    {
        return $this->values[self::DISPLAY_NAME] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->values[self::USERNAME] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->values[self::EMAIL] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getActive()
    {
        return $this->values[self::ACTIVE] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getLocked()
    {
        return $this->values[self::LOCKED] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getManagerEmail()
    {
        return $this->values[self::MANAGER_EMAIL] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getRequireMfa()
    {
        return $this->values[self::REQUIRE_MFA] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getSpouseEmail()
    {
        return $this->values[self::SPOUSE_EMAIL] ?? null;
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
        
        return in_array($lowercasedValue, ['true', 'yes', '1'], true);
    }
    
    protected function setLocked($input)
    {
        if ($input === null) {
            return;
        }
        
        $this->values[self::LOCKED] = $this->isAffirmative($input) ? 'yes' : 'no';
    }
    
    protected function setRequireMfa($input)
    {
        if ($input === null) {
            return;
        }
        
        $this->values[self::REQUIRE_MFA] = $this->isAffirmative($input) ? 'yes' : 'no';
    }
    
    /**
     * Get this User object's data as an associative array.
     *
     * NOTE: This will return all fields that have been explicitly set,
     * regardless of what value they were set to (even null).
     *
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }
}
