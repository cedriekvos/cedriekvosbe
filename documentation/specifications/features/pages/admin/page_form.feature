Feature: Admin page editor
  In order to publish and maintain standalone site pages
  As a signed-in editor
  I want one form that creates new pages and edits existing ones

  Background:
    Given I am signed in as an editor

  # Admin page editor 01
  Scenario: A new page starts as a draft
    When I open the new-page form
    Then the slug should be empty
    And the page should be marked as a draft by default

  # Admin page editor 02
  Scenario: Creating a published page
    When I open the new-page form
    And I fill in the page with:
      | title | About     |
      | slug  | about     |
      | body  | # Hello   |
    And I mark it as published
    And I save
    Then the page should be stored under the slug "about"
    And I should see the confirmation "Page created."
    And I should be returned to the page list

  # Admin page editor 03
  Scenario: Saving a draft prefixes the stored slug with "draft-"
    When I create a page with slug "wip" and leave it marked as a draft
    Then the page should be stored under the slug "draft-wip"

  # Admin page editor 04
  Scenario: Editing an existing page
    Given a published page exists with slug "editable"
    When I open the editor for the page "editable"
    And I change the title to "Updated title"
    And I save
    Then I should see the confirmation "Page updated."

  # Admin page editor 05
  Scenario: Required fields are validated
    When I open the new-page form
    And I save without filling in the title, slug or body
    Then I should see validation errors for "title", "slug" and "body"

  # Admin page editor 06
  Scenario Outline: The slug only accepts lowercase letters, numbers and hyphens
    When I open the new-page form
    And I enter the slug "<slug>"
    And I save
    Then I should see a validation error for the slug
    Examples:
      | slug        |
      | Hello       |
      | hello world |
      | hello_world |
      | draft       |
      | draft-wip   |

  # Admin page editor 07
  Scenario: A slug carrying the draft prefix is rejected
    When I create a published page with slug "draft-release-notes"
    Then I should see a validation error for the slug
    And no page should be stored

  # Admin page editor 08
  Scenario: A slug that already exists is rejected
    Given a published page exists with slug "taken"
    When I create another page with slug "taken"
    Then I should see the error "A page with this slug already exists."

  # Admin page editor 09
  Scenario: Opening the editor for a page that does not exist returns not found
    When I open the editor for the page "ghost"
    Then I should receive a 404 not found response

  # Admin page editor 10
  Scenario Outline: The page form cannot be reached while signed out
    Given I am not signed in
    When I open <screen>
    Then I should be redirected to the login page

    Examples:
      | screen                      |
      | the new-page form           |
      | the editor for page "ghost" |

  # Admin page editor 11
  Scenario: Leaving the title fills in an empty slug
    When I open the new-page form
    And I fill in the title with "My Great Page"
    And I leave the title field
    Then the slug should be "my-great-page"

  # Admin page editor 12
  Scenario: Leaving the title does not overwrite an existing slug
    When I open the new-page form
    And I enter the slug "custom-slug"
    And I fill in the title with "My Great Page"
    And I leave the title field
    Then the slug should be "custom-slug"
