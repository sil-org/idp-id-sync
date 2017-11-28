
Feature: Standardizing user info

  Scenario Outline: Sanitizing input values
    Given I create a User with a <field> value of <input>
    When I get the info from that User
    Then the <field> value should be <output>

    Examples:
      | field       | input   | output |
      | locked      | 'no'    | 'no'   |
      | locked      | 'false' | 'no'   |
      | locked      | 'False' | 'no'   |
      | locked      | 'FALSE' | 'no'   |
      | locked      | false   | 'no'   |
      | locked      | 'other' | 'no'   |
      | locked      | 'yes'   | 'yes'  |
      | locked      | 'true'  | 'yes'  |
      | locked      | 'True'  | 'yes'  |
      | locked      | 'TRUE'  | 'yes'  |
      | locked      | true    | 'yes'  |
      | locked      | null    | null   |
      | require_mfa | 'no'    | 'no'   |
      | require_mfa | 'false' | 'no'   |
      | require_mfa | 'False' | 'no'   |
      | require_mfa | 'FALSE' | 'no'   |
      | require_mfa | false   | 'no'   |
      | require_mfa | 'other' | 'no'   |
      | require_mfa | 'yes'   | 'yes'  |
      | require_mfa | 'true'  | 'yes'  |
      | require_mfa | 'True'  | 'yes'  |
      | require_mfa | 'TRUE'  | 'yes'  |
      | require_mfa | true    | 'yes'  |
      | require_mfa | null    | null   |
