
Feature: Synchronizing records

  # Ad-hoc synchronization scenarios:

  Scenario: User exists in both the ID Store and the ID Broker
    Given a specific user exists in the ID Store
      And the user exists in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then the user should exist in the ID Broker

  Scenario: User exists in the ID Store but not the ID Broker
    Given a specific user exists in the ID Store
      But the user does not exist in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then the user should exist in the ID Broker
      And the user info in the ID Broker and the ID Store should match

  Scenario: User exists in the ID Broker but not the ID Store
    Given a specific user exists in the ID Broker
      But the user does not exist in the ID Store
    When I learn the user does not exist in the ID Store and I tell the ID Broker
    Then the user should be inactive in the ID Broker

  Scenario: User does not exist in the ID Store or the ID Broker
    Given a specific user does not exist in the ID Store
      And the user does not exist in the ID Broker
    When I learn the user does not exist in the ID Store and I tell the ID Broker
    Then the user should not exist in the ID Broker

  Scenario: User info in ID Broker does not equal user info in ID Store
    Given a specific user exists in the ID Store
      And the user exists in the ID Broker
      And the user info in the ID Broker does not match the user info in the ID Store
    When I get the user info from the ID Store and send it to the ID Broker
    Then the user should exist in the ID Broker
      And the user info in the ID Broker and the ID Store should match

  # Full batch synchronization scenarios:

  Scenario: Update a user in the ID Broker
    Given ONLY the following users are active in the ID Store:
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
    Given ONLY the following users are active in the ID Store:
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
    Given ONLY the following users are active in the ID Store:
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
    Given ONLY the following users are active in the ID Store:
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

  # Incremental batch synchronization scenarios:

  Scenario: Syncing users changed since a specific point in time
    Given the ID Store has the following log of when users were changed:
        | changedAt   | employeeNumber |
        | 1491400000  | 10001          |
        | 1491400700  | 10003          |
        | 1491400800  | 10002          |
        | 1491400900  | 10004          |
      And ONLY the following users are active in the ID Store:
        | employeeNumber | displayName    | username     |
        | 10001          | Unchanged User | person_one   |
        | 10002          | Changed User   | person_two   |
        | 10004          | Added User     | person_four  |
        | 10005          | Missed User    | person_five  |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name   | username     | active |
        | 10001          | Unchanged User | person_one   | yes    |
        | 10002          | User To Change | person_two   | yes    |
        | 10003          | Removed User   | person_three | yes    |
    When I ask the ID Store for the list of users changed since 1491400600 and sync them
    Then ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Unchanged User | person_one   | yes    |
        | 10002          | Changed User   | person_two   | yes    |
        | 10003          | Removed User   | person_three | no     |
        | 10004          | Added User     | person_four  | yes    |
