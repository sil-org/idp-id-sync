
Feature: Integration with IdP ID Broker

  Scenario: Create user
    Given a user does not exist
    When I create that user
    Then that user should now exist

  Scenario: Deactivate user
    Given an active user exists
    When I deactivate that user
    Then that user should now NOT be active

  Scenario: Get user
    Given an active user exists
    When I get that user
    Then I should receive back information about that user

  Scenario: List users
    Given at least 3 users exist
    When I get the list of users
    Then I should receive a list of at least 3 users
      And each entry in the resulting list should have user information

  Scenario: Update user
    Given an active user exists
    When I update that user
    Then when I get that user I should receive the updated information
