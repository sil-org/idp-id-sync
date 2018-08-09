
Feature: Integration with a live ID Store

  @integration @specificUser
  Scenario: Asking for a specific (active) user
    Given I can make authenticated calls to the ID Store
    When I ask the ID Store for a specific active user
    Then I should get back information about that user

  @integration @allUsers
  Scenario: Asking for all (active) users
    Given I can make authenticated calls to the ID Store
    When I ask the ID Store for all active users
    Then I should get back a list of information about active users

  @integration @usersChangedSince
  Scenario: Asking for (active) users changed since a particular time
    Given I can make authenticated calls to the ID Store
    When I ask the ID Store for all users changed since a specific point in time
    Then I should get back a list of information about changed users
