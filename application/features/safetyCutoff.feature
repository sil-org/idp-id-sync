
Feature: Preventing too many changes at once to protect against iffy data.

  Scenario Outline: Preventing too many user-deactivations at a time
    Given <activeInBroker> users are active in the ID Broker
      And running a full sync would deactivate <numToDeactivate> users
      And the safety cutoff is <safetyCutoff>
    When I sync all the users from the ID Store to the ID Broker
    Then an exception <exceptionOrNot> have been thrown

    Examples:
      | activeInBroker | numToDeactivate | safetyCutoff | exceptionOrNot |
      |       10       |      0          |     0.15     |  should NOT    |
      |       10       |      1          |     0.15     |  should NOT    |
      |       10       |      2          |     0.15     |  should NOT    |
      |       10       |      3          |     0.15     |  SHOULD        |
      |       10       |      4          |     0.15     |  SHOULD        |
      |       10       |      5          |     0.15     |  SHOULD        |
      |       10       |      6          |     0.15     |  SHOULD        |
      |       10       |      7          |     0.15     |  SHOULD        |
      |       10       |      8          |     0.15     |  SHOULD        |
      |       10       |      9          |     0.15     |  SHOULD        |
      |       10       |     10          |     0.15     |  SHOULD        |
      |       10       |      5          |     0.60     |  should NOT    |
      |       10       |      6          |     0.60     |  should NOT    |
      |       10       |      7          |     0.60     |  SHOULD        |
      |       10       |      8          |     0.60     |  SHOULD        |
      |        2       |      0          |    -1        |  SHOULD        |
      |        2       |      0          |     0        |  should NOT    |
      |        2       |      0          |     1        |  should NOT    |
      |        2       |      0          |     0.99     |  should NOT    |
      |        2       |      0          |     1.00     |  should NOT    |
      |        2       |      0          |     1.01     |  SHOULD        |
      |        2       |      0          |     abcd     |  SHOULD        |

  Scenario Outline: Preventing too many user-creations at a time
    Given <activeInBroker> users are active in the ID Broker
      And running a full sync would create <numToCreate> users
      And the safety cutoff is <safetyCutoff>
    When I sync all the users from the ID Store to the ID Broker
    Then an exception <exceptionOrNot> have been thrown

    Examples:
      | activeInBroker | numToCreate | safetyCutoff | exceptionOrNot |
      |       10       |      0      |     0.15     |  should NOT    |
      |       10       |      1      |     0.15     |  should NOT    |
      |       10       |      2      |     0.15     |  should NOT    |
      |       10       |      3      |     0.15     |  SHOULD        |
      |       10       |      4      |     0.15     |  SHOULD        |
      |       10       |      5      |     0.15     |  SHOULD        |
      |       10       |      6      |     0.15     |  SHOULD        |
      |       10       |      7      |     0.15     |  SHOULD        |
      |       10       |      8      |     0.15     |  SHOULD        |
      |       10       |      9      |     0.15     |  SHOULD        |
      |       10       |     10      |     0.15     |  SHOULD        |

