
Feature: Integration with Insite

  @integration
  Scenario: Asking for a specific (active) user
    Given I can make authenticated calls to Insite
    When I ask Insite for a specific active user
    Then I should get back information about that user

  @integration
  Scenario: Asking for all (active) users
    Given I can make authenticated calls to Insite
    When I ask Insite for all active users
    Then I should get back a list of information about active users

  @integration
  Scenario: Asking for (active) users changed since a particular time
    Given I can make authenticated calls to Insite
    When I ask Insite for all users changed since a specific point in time
    Then I should get back a list of information about changed users
