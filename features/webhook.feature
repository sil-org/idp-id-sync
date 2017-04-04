
Feature: Receiving webhook notifications

  Scenario Outline: Receiving a notification
    Given the notification URL path will be <urlPath>
    When ID Sync receives the notification
    Then it should return a status code of <responseCode>

    Examples:
      | urlPath                 | responseCode |
      | '/user/change/0'        | 422          |
      | '/user/change/321123'   | 200          |
