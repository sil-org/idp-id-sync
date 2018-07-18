
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

  Scenario: User has a spouse email address in ID Broker but not in ID Store
    Given a specific user exists in the ID Store
      And the user exists in the ID Broker
      And the user has a spouse email address in the ID Broker
      But the user does not have a spouse email address in the ID Store
    When I get the user info from the ID Store and send it to the ID Broker
    Then the user should exist in the ID Broker
      And the user should not have a spouse email address in the ID Broker

  # Full batch synchronization scenarios:

  Scenario: Update a user in the ID Broker
    Given ONLY the following users are active in the ID Store:
        | employeenumber | displayname  | username   |
        | 10001          | Nickname     | first_last |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | First Last   | first_last | yes    |
    When I sync all the users from the ID Store to the ID Broker
    Then an exception should NOT have been thrown
      And ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Nickname     | first_last | yes    |

  Scenario: Add a user to the ID Broker
    Given ONLY the following users are active in the ID Store:
        | employeenumber | displayname  | username   |
        | 10001          | Person One   | person_one |
        | 10002          | Person Two   | person_two |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
    When I sync all the users from the ID Store to the ID Broker
    Then an exception should NOT have been thrown
      And ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
        | 10002          | Person Two   | person_two | yes    |

  Scenario: Handling a sync creation error gracefully
    Given 5 users are active in the ID Store
      And NO users exist in the ID Broker
      And user 3 in the list from ID Store will be rejected by the ID Broker
    When I sync all the users from the ID Store to the ID Broker
    Then an exception should NOT have been thrown
      And the ID Broker should now have 4 active users.

  Scenario: Handling a sync update error gracefully
    Given 5 users are active in the ID Store and are inactive in the ID Broker
      And user 3 in the list from ID Store will be rejected by the ID Broker
    When I sync all the users from the ID Store to the ID Broker
    Then an exception should NOT have been thrown
      And the ID Broker should now have 4 active users.

  Scenario: Handling sync errors gracefully (in more detail)
    Given ONLY the following users are active in the ID Store:
        | employeenumber | displayname     | username     | email          |
        | 10001          | Good Update     | person_one   | p1@example.com |
        | 10002          | Bad Create      | person_two   |                |
        | 10003          | Bad Update      | person_three |                |
        | 10004          | Good After Bad  | person_four  | p4@example.com |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name    | username     | email          | active |
        | 10001          | One to Update   | person_one   | p1@example.com | yes    |
        | 10003          | Three to Update | person_three | p3@example.com | yes    |
    When I sync all the users from the ID Store to the ID Broker
    Then an exception should NOT have been thrown
      And ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name    | username     | email          | active |
        | 10001          | Good Update     | person_one   | p1@example.com | yes    |
        | 10003          | Three to Update | person_three | p3@example.com | yes    |
        | 10004          | Good After Bad  | person_four  | p4@example.com | yes    |

  Scenario: Activate a user in ID Broker
    Given ONLY the following users are active in the ID Store:
        | employeenumber | displayname  | username   |
        | 10001          | Person One   | person_one |
        | 10002          | Person Two   | person_two |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
        | 10002          | Person Two   | person_two | no     |
    When I sync all the users from the ID Store to the ID Broker
    Then an exception should NOT have been thrown
      And ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username   | active |
        | 10001          | Person One   | person_one | yes    |
        | 10002          | Person Two   | person_two | yes    |

  Scenario: Deactivate a user in ID Broker
    Given ONLY the following users are active in the ID Store:
        | employeenumber | displayname  | username     |
        | 10002          | Person Two   | person_two   |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name | username     | active |
        | 10001          | Person One   | person_one   | yes    |
        | 10002          | Person Two   | person_two   | yes    |
        | 10003          | Person Three | person_three | no     |
    When I sync all the users from the ID Store to the ID Broker
    Then an exception should NOT have been thrown
      And ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name | username     | active |
        | 10001          | Person One   | person_one   | no     |
        | 10002          | Person Two   | person_two   | yes    |
        | 10003          | Person Three | person_three | no     |

  # Incremental batch synchronization scenarios:

  Scenario: Syncing users changed since a specific point in time
    Given the ID Store has the following log of when users were changed:
        | changedat   | employeenumber |
        | 1491400000  | 10001          |
        | 1491400700  | 10003          |
        | 1491400800  | 10002          |
        | 1491400900  | 10004          |
      And ONLY the following users are active in the ID Store:
        | employeenumber | displayname    | username     |
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

  Scenario: Syncing users changed since a specific point in time despite a sync error
    Given the ID Store has the following log of when users were changed:
        | changedat   | employeenumber |
        | 1491401000  | 10001          |
        | 1491402000  | 10002          |
        | 1491403000  | 10003          |
      And ONLY the following users are active in the ID Store:
        | employeenumber | displayname    | username     | email          |
        | 10001          | Unchanged 1    | person_one   | p1@example.com |
        | 10002          | Changed 2      | person_two   |                |
        | 10003          | Changed 3      | person_three | p3@example.com |
      And ONLY the following users exist in the ID Broker:
        | employee_id    | display_name   | username     | email          | active |
        | 10001          | Unchanged 1    | person_one   | p1@example.com | yes    |
        | 10002          | Original 2     | person_two   | p2@example.com | yes    |
        | 10003          | Original 3     | person_three | p3@example.com | yes    |
    When I ask the ID Store for the list of users changed since 1491401999 and sync them
    Then ONLY the following users should exist in the ID Broker:
        | employee_id    | display_name   | username     | email          | active |
        | 10001          | Unchanged 1    | person_one   | p1@example.com | yes    |
        | 10002          | Original 2     | person_two   | p2@example.com | yes    |
        | 10003          | Changed 3      | person_three | p3@example.com | yes    |
