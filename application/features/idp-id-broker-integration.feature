
Feature: Integration with IdP ID Broker

  Scenario: Activate user
    Given a user exists
      And that user is not active
    When I activate that user
    Then that user should now be active

  Scenario: Authenticate
    Given a user exists
      And that user has a password
    When I try to authenticate as that user
    Then I should receive back information about that user

  Scenario: Create user
    Given a user does not exist
    When I create that user
    Then that user should now exist

  Scenario: Deactivate user
    Given a user exists
      And that user is active
    When I deactivate that user
    Then that user should now NOT be active

  Scenario: Get user
    Given a user exists
    When I get that user
    Then I should receive back information about that user

  Scenario: List users
    Given at least 3 users exist
    When I get the list of users
    Then I should receive a list of at least 3 users
      And each entry in the resulting list should have user information

  Scenario: Set password
    Given a user exists
      And that user has a password
    When I set that user's password to something else
    Then I should NOT be able to authenticate with the old password
      And I SHOULD be able to authenticate with the new password

  Scenario: Update user
    Given a user exists
    When I update that user
    Then when I get that user I should receive the updated information
