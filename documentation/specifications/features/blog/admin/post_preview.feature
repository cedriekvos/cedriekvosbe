Feature: Admin post preview
  In order to check how a draft post will look before it goes live
  As a signed-in editor
  I want a preview button that shows the post as visitors would see it, on a URL that only works while I am signed in

  Background:
    Given I am signed in as an editor

  # Admin post preview 01
  Scenario Outline: A preview button is offered in the editor of a saved post
    Given a <status> post exists with slug "welcome"
    When I open the editor for "welcome"
    Then I should see a preview button
    And the preview button should link to "/admin/posts/welcome/preview"
    And the preview button should open in a new tab

    Examples:
      | status    |
      | draft     |
      | published |

  # Admin post preview 01b
  Scenario: The post list has no preview buttons
    Given a draft post exists with slug "welcome"
    When I open the admin post list
    Then I should not see a preview button

  # Admin post preview 02
  Scenario: The post creation form has no preview button
    When I open the new post form
    Then I should not see a preview button

  # Admin post preview 03
  Scenario Outline: Previewing a post shows it as visitors would see it
    Given a <status> post exists with:
      | title   | slug    | date       |
      | Welcome | welcome | 2026-05-01 |
    When I navigate to "/admin/posts/welcome/preview"
    Then I should see the post title "Welcome"
    And I should see the publication date "01/05/2026"
    And I should see the article content rendered from Markdown

    Examples:
      | status    |
      | draft     |
      | published |

  # Admin post preview 04
  Scenario: A preview is clearly marked as a preview
    Given a draft post exists with slug "welcome"
    When I navigate to "/admin/posts/welcome/preview"
    Then I should see a notice that this is a preview

  # Admin post preview 05
  Scenario: A draft post stays hidden on its public URL
    Given a draft post exists with slug "welcome"
    When I navigate to "/blog/welcome" while signed in
    Then I should receive a 404 not found response

  # Admin post preview 06
  Scenario: Previewing an unknown post returns not found
    Given no post exists with slug "does-not-exist"
    When I navigate to "/admin/posts/does-not-exist/preview"
    Then I should receive a 404 not found response

  # Admin post preview 07
  Scenario Outline: A slug that could address something other than a post returns not found
    When I navigate to "/admin/posts/<slug>/preview"
    Then I should receive a 404 not found response

    Examples:
      | slug           |
      | ..             |
      | ..%2F..%2F.env |
      | .env           |
      | Welcome        |
      | not_a_slug     |

  # Admin post preview 08
  Scenario: A preview cannot be opened while signed out
    Given I am not signed in
    And a draft post exists with slug "welcome"
    When I navigate to "/admin/posts/welcome/preview"
    Then I should be redirected to the login page
    And I should not see the draft's content

  # Admin post preview 09
  Scenario: Raw HTML in a previewed post is escaped rather than rendered
    Given a draft post exists with slug "welcome" whose body contains "<script>alert(1)</script>"
    When I navigate to "/admin/posts/welcome/preview"
    Then I should see the raw HTML as visible text
    And the script should not be part of the page markup
