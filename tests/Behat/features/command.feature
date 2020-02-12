Feature: Command
  In order to command fioul
  As an user
  I need to be able to command or not in the limited available fioul quantity

  Scenario: Try to command fioul quantity too small
    Given I am on "/commande-fioul"
    When I fill in "quantity" with "200"
    And I press "btn-commande"
    Then I should see "Veuillez saisir entre 500 et 10000 Litres"

  Scenario: Try to command fioul quantity too big
    Given I am on "/commande-fioul"
    When I fill in "quantity" with "20000"
    And I press "btn-commande"
    Then I should see "Veuillez saisir entre 500 et 10000 Litres"

  @javascript
  Scenario: Command fioul successfull
    Given I have in the database entities "\AppBundle\Entity\TruckDay" with values:
      | date          | truck  | capacity | postalCode |
      | today + 2days | _BEHAT | 9000     | 92500      |
      | today + 3days | _BEHAT | 9000     | 92500      |
      | today + 4days | _BEHAT | 9000     | 92500      |
      | today + 5days | _BEHAT | 9000     | 92500      |
      | today + 6days | _BEHAT | 9000     | 92500      |
    Given I am on "/commande-fioul"
    Then I wait 1 seconds
    And I should see "9000 Litres" in the ".case" element
    When I fill in "quantity" with "6000"
    And I press "btn-commande"
    Then I wait 1 seconds
    And I should see "3000 Litres" in the ".case" element
    When I select ".case" element
    And I press "btn-commande"
    Then I shoud have in the database "1" entity "\AppBundle\Entity\Commande" with values:
      | quantity | date  | status      |
      | 6000     | today | in_progress |
    And I should be on "/confirmation-commande-fioul"
    And I should see "6000 Litres" in the ".card-content" element
    When I press "btn-commande"
    Then I shoud have in the database "1" entity "\AppBundle\Entity\Commande" with values:
      | quantity | date  | status |
      | 6000     | today | done   |
    And I should be on "/confirmation-commande-fioul"
    And I should see "Merci pour votre commande." in the ".card-action" element
    When I follow "Nouvelle commande"
    And I should be on "/commande-fioul"
    Then I wait 1 seconds
    And I should see "3000 Litres" in the ".case" element

  @javascript
  Scenario: Command fioul full
    Given I have in the database entities "\AppBundle\Entity\TruckDay" with values:
      | date          | truck  | capacity | postalCode |
      | today + 2days | _BEHAT | 1000     | 92500      |
      | today + 3days | _BEHAT | 1000     | 92500      |
      | today + 4days | _BEHAT | 1000     | 92500      |
      | today + 5days | _BEHAT | 1000     | 92500      |
      | today + 6days | _BEHAT | 1000     | 92500      |
    Given I am on "/commande-fioul"
    Then I wait 1 seconds
    And I should see "1000 Litres" in the ".case" element
    When I fill in "quantity" with "2000"
    And I press "btn-commande"
    Then I wait 1 seconds
    And I should see "Complet" in the ".case" element
