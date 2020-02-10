Feature: Import JSON
  In order to import JSON files of trucks and trucks days
  As a CRON job
  I need to put the JSON data into the database

  Scenario: Importing JSON files successfully
    Given there is a "behat_camions.json" into the "tests/Fixtures/" folder wich contains :
    """

    """
    When I execute the command "app:import-truckdays"
    Then I shoud have in the database an entry in the "truckday" table with the "date, truck, capacity" columns equals to "2020-02-01, 4CV8F, 9000"