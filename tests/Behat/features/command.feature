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
    Then I wait 1 seconds
    Then I should see "11100 Litres" in the "#calendar-188" element
    Then I should see "5300 Litres" in the "#calendar-191" element
    Then I should see "6000 Litres" in the "#calendar-194" element
    When I fill in "quantity" with "6000"
    And I press "btn-commande"
    Then I wait 1 seconds
    Then I should see "5100 Litres" in the "#calendar-188" element
    Then I should see "Complet" in the "#calendar-191" element
    Then I should see "0 Litres" in the "#calendar-194" element

