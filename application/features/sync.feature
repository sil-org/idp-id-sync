
Feature: Synchronizing a record

  # Overview / big picture secenarios:

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
