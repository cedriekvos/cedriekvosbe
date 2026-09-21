Feature: Admin page list
  In order to manage the site's standalone pages
  As a signed-in editor
  I want an overview of every page — including drafts — with quick actions to edit and delete

  Background:
    Given I am signed in as an editor

  # Admin page list 01
  Scenario: Every page is listed, including drafts
    Given the following pages exist:
      | title          | status    |
      | Public page    | published |
      | Hidden draft   | draft     |
    When I open the admin page list
    Then I should see "Public page"
    And I should see "Hidden draft"
    And "Hidden draft" should be marked with a "draft" label

  # Admin page list 02
  Scenario: An empty list explains there are no pages yet
    Given no pages exist
    When I open the admin page list
    Then I should see "No pages yet."

  # Admin page list 03
  Scenario: Deleting a page
    Given a published page exists with slug "goner"
    When I open the admin page list
    And I delete the page "goner"
    Then the page "goner" should no longer exist on disk
    And I should see the confirmation "Page [goner] deleted."

  # Admin page list 04
  Scenario: Reaching the editor from the list
    Given a published page exists with slug "welcome"
    When I open the admin page list
    Then I should see a link to create a new page
    And I should see a link to edit the page "welcome"

  # Admin page list 05
  Scenario: The page list cannot be reached while signed out
    Given I am not signed in
    When I open the admin page list
    Then I should be redirected to the login page
