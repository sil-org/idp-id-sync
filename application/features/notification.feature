
Feature: Sending notifications

  Scenario: Sending a notification
    Given at least one user has no email address
    When I call the sendMissingEmailNotice function
    Then an email is sent

  Scenario: Don't send missing email notification when the ID Store user has an email address
    Given a specific user exists in the ID Store with an email address
      But the user does not exist in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then an email with subject "Email address missing" is not sent

  Scenario: Missing email notification
    Given a specific user exists in the ID Store without an email address
      But the user does not exist in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then an email is sent
      And the email subject contains "Email address missing"

  Scenario: New user email notification - enabled
    Given new user email notifications are enabled
      And a specific user exists in the ID Store
      But the user does not exist in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then a "New user" email is sent to the user's HR contact

  Scenario: New user email notification - disabled
    Given new user email notifications are disabled
      And a specific user exists in the ID Store
      But the user does not exist in the ID Broker
    When I get the user info from the ID Store and send it to the ID Broker
    Then an email with subject "New user" is not sent
