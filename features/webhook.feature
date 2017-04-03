
Feature: Receiving webhook notifications

  Scenario Outline: Receiving various notifications
    Given a notification to "/user/change" contains <requestBody>
    When ID Sync receives the notification
    Then it should return a status code of <responseCode>

    Examples:
      | requestBody                  | responseCode |
      | '{"employeeNumber": 321123}' | 200          |
      | '{"employeeNumber": ""}'     | 422          |
      | '{}'                         | 422          |
      | ''                           | 422          |
