Feature: Import JSON
  In order to import JSON files of trucks and trucks days
  As a CRON job
  I need to put the JSON data into the database

  Scenario: Importing JSON files successfully
    Given there is a "behat_camions.json" file into the "app/cache/test/" folder wich contains :
    """
{
  "department": "92",
  "trucks": [
    {
      "date": "2020-02-01",
      "capacity": 9000,
      "truck": "_BEHAT"
    }
  ]
}
    """
    Given there is a "behat_creneaux.json" file into the "app/cache/test/" folder wich contains :
    """
{
  "postal_code": "92500",
  "insee_code": "92063",
  "month": "02",
  "slots": [
    {
      "date": "2020-02-01",
      "truck": "_BEHAT"
    }
  ]
}
    """
    When I execute the command "app:import-truckdays" with options:
    | --trucks    | app/cache/test/behat_camions.json  |
    | --truckdays | app/cache/test/behat_creneaux.json |
    Then the output should contains "1 créneaux importés"
    Then I shoud have in the database "1" entity "\AppBundle\Entity\TruckDay" with values:
    | date       | truck  | capacity |
    | 2020-02-01 | _BEHAT | 9000     |