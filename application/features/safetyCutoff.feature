
Feature: Preventing too many changes at once to protect against iffy data.

  Scenario Outline: Preventing too many user-deactivations at a time
    Given <brokerHas> users are active in the ID Broker
      And only <storeOnlyHas> users are active in the ID Store
      And the cutoff for deactivations is <cutoffPercent>
    When I sync all the users from the ID Store to the ID Broker
    Then an exception <exceptionOrNot> have been thrown

    Examples:
      | brokerHas | storeOnlyHas | cutoffPercent | exceptionOrNot |
      |  10       |  10          |   0.15        |  should NOT    |
      |  10       |   9          |   0.15        |  should NOT    |
      |  10       |   8          |   0.15        |  should NOT    |
      |  10       |   7          |   0.15        |  SHOULD        |
      |  10       |   6          |   0.15        |  SHOULD        |
      |  10       |   5          |   0.15        |  SHOULD        |
      |  10       |   4          |   0.15        |  SHOULD        |
      |  10       |   3          |   0.15        |  SHOULD        |
      |  10       |   2          |   0.15        |  SHOULD        |
      |  10       |   1          |   0.15        |  SHOULD        |
      |  10       |   0          |   0.15        |  SHOULD        |
      |  10       |   5          |   0.60        |  should NOT    |
      |  10       |   4          |   0.60        |  should NOT    |
      |  10       |   3          |   0.60        |  SHOULD        |
      |  10       |   2          |   0.60        |  SHOULD        |
      |   2       |   2          |  -1           |  SHOULD        |
      |   2       |   2          |   0           |  should NOT    |
      |   2       |   2          |   1           |  should NOT    |
      |   2       |   2          |   0.99        |  should NOT    |
      |   2       |   2          |   1.00        |  should NOT    |
      |   2       |   2          |   1.01        |  SHOULD        |
      |   2       |   2          |   abcd        |  SHOULD        |
