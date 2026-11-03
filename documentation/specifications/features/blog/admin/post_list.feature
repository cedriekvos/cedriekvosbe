Feature: Admin post list
  In order to manage the blog's content
  As a signed-in editor
  I want an overview of every post — including drafts — with quick actions to edit and delete

  Background:
    Given I am signed in as an editor

  Scenario: Every post is listed, including drafts
    Given the following posts exist:
      | title         | status    |
      | Public update | published |
      | Hidden draft  | draft     |
    When I open the admin post list
    Then I should see "Public update"
    And I should see "Hidden draft"
    And "Hidden draft" should be marked with a "draft" label

  Scenario: Each listed post shows its publication date as day/month/year
    Given a published post exists with:
      | title         | slug          | date       |
      | Public update | public-update | 2026-05-01 |
    When I open the admin post list
    Then I should see the publication date "01/05/2026"

  Scenario: An empty list explains there are no posts yet
    Given no posts exist
    When I open the admin post list
    Then I should see "No posts yet."

  Scenario: Deleting a post
    Given a published post exists with slug "goner"
    When I open the admin post list
    And I delete the post "goner"
    Then the post "goner" should no longer exist on disk
    And I should see the confirmation "Post [goner] deleted."

  Scenario: Reaching the editor from the list
    Given a published post exists with slug "welcome"
    When I open the admin post list
    Then I should see a link to create a new post
    And I should see a link to edit "welcome"

  Scenario: The post list cannot be reached while signed out
    Given I am not signed in
    When I open the admin post list
    Then I should be redirected to the login page
