
Feature: Sending notifications

  Scenario: Sending a notification
    Given at least one user has no email address
    When I call the sendMissingEmailNotice function
    Then an email is sent

  Scenario: Missing email notification
    Given a specific user exists in the ID Store without an email address
      But the user does not exist in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then an email is sent
