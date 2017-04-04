
Feature: Receiving webhook notifications

  Scenario Outline: Receiving various notifications
    Given the notification URL path will be <urlPath>
    When ID Sync receives the notification
    Then it should return a status code of <responseCode>

    Examples:
      | urlPath                 | responseCode |
      | '/user/change/321123'   | 200          |
      | '/user/change/321123/a' | 404          |
