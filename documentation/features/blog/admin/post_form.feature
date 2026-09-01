Feature: Admin post editor
  In order to publish and maintain blog content
  As a signed-in editor
  I want one form that creates new posts and edits existing ones

  Background:
    Given I am signed in as an editor

  Scenario: A new post starts as a draft dated today
    When I open the new-post form
    Then the date should default to today
    And the slug should be empty
    And the post should be marked as a draft by default

  Scenario: Creating a published post
    When I open the new-post form
    And I fill in the post with:
      | title | My first post |
      | slug  | my-first-post |
      | date  | 2026-05-13    |
      | body  | # Hello       |
    And I mark it as published
    And I save
    Then the post should be stored under the slug "my-first-post"
    And I should see the confirmation "Post created."
    And I should be returned to the post list

  Scenario: Saving a draft prefixes the stored slug with "draft-"
    When I create a post with slug "wip" and leave it marked as a draft
    Then the post should be stored under the slug "draft-wip"

  Scenario: Editing an existing post
    Given a published post exists with slug "editable"
    When I open the editor for "editable"
    And I change the title to "Updated title"
    And I save
    Then I should see the confirmation "Post updated."

  Scenario: Required fields are validated
    When I open the new-post form
    And I save without filling in the title, slug, date or body
    Then I should see validation errors for "title", "slug", "date" and "body"

  Scenario Outline: The slug only accepts lowercase letters, numbers and hyphens
    When I open the new-post form
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

  Scenario: A slug carrying the draft prefix is rejected
    When I create a published post with slug "draft-release-notes"
    Then I should see a validation error for the slug
    And no post should be stored

  Scenario: A slug that already exists is rejected
    Given a published post exists with slug "taken"
    When I create another post with slug "taken"
    Then I should see the error "A post with this slug already exists."

  Scenario: Opening the editor for a post that does not exist returns not found
    When I open the editor for "ghost"
    Then I should receive a 404 not found response

  Scenario Outline: The post form cannot be reached while signed out
    Given I am not signed in
    When I open <screen>
    Then I should be redirected to the login page

    Examples:
      | screen                 |
      | the new-post form      |
      | the editor for "ghost" |

  # Admin post editor 10
  Scenario: A new post starts as not featured by default
    When I open the new-post form
    Then the post should not be marked as featured by default

  # Admin post editor 11
  Scenario: Creating a post marked as featured
    When I open the new-post form
    And I fill in the post with:
      | title | My first post |
      | slug  | my-first-post |
      | date  | 2026-05-13    |
      | body  | # Hello       |
    And I mark it as published
    And I mark it as featured
    And I save
    Then the post "my-first-post" should be stored as featured

  # Admin post editor 12
  Scenario: The featured toggle reflects the post's stored state when editing
    Given a featured post exists with slug "editable"
    When I open the editor for "editable"
    Then the post should be marked as featured

  # Admin post editor 13
  Scenario: Un-marking a featured post
    Given a featured post exists with slug "editable"
    When I open the editor for "editable"
    And I unmark it as featured
    And I save
    Then the post "editable" should be stored as not featured

  # Admin post editor 14
  Scenario: More than one post can be featured at the same time
    Given a featured post exists with slug "first-featured"
    When I open the new-post form
    And I fill in the post with:
      | title | Second featured |
      | slug  | second-featured |
      | date  | 2026-05-14      |
      | body  | # Hello again   |
    And I mark it as published
    And I mark it as featured
    And I save
    Then the post "first-featured" should still be stored as featured
    And the post "second-featured" should be stored as featured

  # Admin post editor 15
  Scenario: Leaving the title fills in an empty slug
    When I open the new-post form
    And I fill in the title with "My Great Post"
    And I leave the title field
    Then the slug should be "my-great-post"

  # Admin post editor 16
  Scenario: Leaving the title does not overwrite an existing slug
    When I open the new-post form
    And I enter the slug "custom-slug"
    And I fill in the title with "My Great Post"
    And I leave the title field
    Then the slug should be "custom-slug"
