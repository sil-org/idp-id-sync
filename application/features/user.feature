
Feature: Standardizing user info

  Scenario Outline: Sanitizing input values
    Given I create a User with a <field> value of <input>
    When I get the info from that User
    Then the <field> value should be <output>

    Examples:
      | field  | input   | output |
      | locked | 'no'    | 'no'   |
      | locked | 'false' | 'no'   |
      | locked | 'False' | 'no'   |
      | locked | 'FALSE' | 'no'   |
      | locked | false   | 'no'   |
      | locked | 'yes'   | 'yes'  |
      | locked | 'true'  | 'yes'  |
      | locked | 'True'  | 'yes'  |
      | locked | 'TRUE'  | 'yes'  |
      | locked | true    | 'yes'  |
      | locked | null    | null   |
