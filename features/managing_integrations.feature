@managing_integrations
Feature: Managing integrations
    In order to keep ma products catalog up to date
    As an Administrator
    I want to be able to manage existing integrations

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Deleting integration
        Given there is an integration named "Integration"
        When I go to the integrations page
        And I delete this integration
        Then I should be notified that the integration has been deleted
        And I should see empty list of integrations

    @ui
    Scenario: Updating integration
        Given there is an integration named "Main integration"
        And integration has category tree code "Sylius category tree code" specified
        When I want to edit this integration
        And I change category tree code to "New main category tree code"
        And I update it
        Then I should be notified that the integration has been successfully updated
        And I should see that "Main integration" has category tree code "New main category tree code" specified

    @ui
    Scenario: Disabling integration
        Given there is an integration named "Unused integration"
        When I want to edit this integration
        And I disable it
        And I update it
        Then I should be notified that the integration has been successfully updated
        And this integration should be disabled

    @ui
    Scenario: Creating with invalid credentials
        When I want go to creation page
        And I set category tree code to "category tree code "
        And I set API URL address to "https://127.0.0.1"
        And I set API key to "Invalid key"
        And I enable it
        And I save it
        Then I should be notified that the integration has invalid credentials
