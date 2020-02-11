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
    Given I am on "/commande-fioul"
    When I fill in "quantity" with "2000"
    And I press "btn-commande"
    Then I should not see "Veuillez saisir entre 500 et 10000 Litres"
    Then I wait 1 seconds

