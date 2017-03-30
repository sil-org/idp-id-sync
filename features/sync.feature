
Feature: Synchronizing a record

  # Ad-hoc synchronization scenarios:

  Scenario: User exists in both the ID Store and the ID Broker
    Given the user exists in the ID Store
      And the user exists in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then the ID Broker response should indicate success

  Scenario: User exists in the ID Store but not the ID Broker
    Given the user exists in the ID Store
      But the user does not exist in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then the ID Broker response should indicate success

  Scenario: User exists in the ID Broker but not the ID Store
    Given the user exists in the ID Broker
      But the user does not exist in the ID Store
    When I learn the user does not exist in the ID Store and I tell the ID Broker
    Then the ID Broker response should indicate success

  Scenario: User does not exist in the ID Store or the ID Broker
    Given the user does not exist in the ID Store
      And the user does not exist in the ID Broker
    When I learn the user does not exist in the ID Store and I tell the ID Broker
    Then the ID Broker response should return an error

  Scenario: User info in ID Broker does not equal user info in ID Store
    Given the user exists in the ID Store
      And the user exists in the ID Broker
      And the user info in the ID Broker does not equal the user info in the ID Store
    When I get the user info from the ID Store and send it to the ID Broker
    Then the ID Broker response should indicate success

  # Full batch synchronization scenarios:

  Scenario: Update a user in the ID Broker
    Given ONLY the following users exist in the ID Store:
        | employeeNumber | displayName  | username   |
        | 10001          | Nickname     | first_last |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | First Last   | first_last | yes    |
    When I sync all the users from the ID Store to the ID Broker
    Then ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Nickname     | first_last | yes    |

  Scenario: Add a user to the ID Broker
    Given ONLY the following users exist in the ID Store:
        | employeeNumber | displayName  | username   |
        | 10001          | Person One   | person_one |
        | 10002          | Person Two   | person_two |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
    When I sync all the users from the ID Store to the ID Broker
    Then ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
        | 10002          | Person Two   | person_two | yes    |

  Scenario: Activate a user in ID Broker
    Given ONLY the following users exist in the ID Store:
        | employeeNumber | displayName  | username   |
        | 10001          | Person One   | person_one |
        | 10002          | Person Two   | person_two |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
        | 10002          | Person Two   | person_two | no     |
    When I sync all the users from the ID Store to the ID Broker
    Then ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
        | 10002          | Person Two   | person_two | yes    |

  Scenario: Deactivate a user in ID Broker
    Given ONLY the following users exist in the ID Store:
        | employeeNumber | displayName  | username   |
        | 10002          | Person Two   | person_two |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
        | 10002          | Person Two   | person_two | yes    |
    When I sync all the users from the ID Store to the ID Broker
    Then ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | no     |
        | 10002          | Person Two   | person_two | yes    |
