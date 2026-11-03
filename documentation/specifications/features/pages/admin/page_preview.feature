Feature: Admin page preview
  In order to check how a draft page will look before it goes live
  As a signed-in editor
  I want a preview button that shows the page as visitors would see it, on a URL that only works while I am signed in

  Background:
    Given I am signed in as an editor

  # Admin page preview 01
  Scenario Outline: A preview button is offered in the editor of a saved page
    Given a <status> page exists with slug "about"
    When I open the editor for "about"
    Then I should see a preview button
    And the preview button should link to "/admin/pages/about/preview"
    And the preview button should open in a new tab

    Examples:
      | status    |
      | draft     |
      | published |

  # Admin page preview 01b
  Scenario: The page list has no preview buttons
    Given a draft page exists with slug "about"
    When I open the admin page list
    Then I should not see a preview button

  # Admin page preview 02
  Scenario: The page creation form has no preview button
    When I open the new page form
    Then I should not see a preview button

  # Admin page preview 03
  Scenario Outline: Previewing a page shows it as visitors would see it
    Given a <status> page exists with:
      | title | slug  |
      | About | about |
    When I navigate to "/admin/pages/about/preview"
    Then I should see the page title "About"
    And I should see the page content rendered from Markdown

    Examples:
      | status    |
      | draft     |
      | published |

  # Admin page preview 04
  Scenario: A preview is clearly marked as a preview
    Given a draft page exists with slug "about"
    When I navigate to "/admin/pages/about/preview"
    Then I should see a notice that this is a preview

  # Admin page preview 05
  Scenario: A draft page stays hidden on its public URL
    Given a draft page exists with slug "about"
    When I navigate to "/about" while signed in
    Then I should receive a 404 not found response

  # Admin page preview 06
  Scenario: Previewing an unknown page returns not found
    Given no page exists with slug "does-not-exist"
    When I navigate to "/admin/pages/does-not-exist/preview"
    Then I should receive a 404 not found response

  # Admin page preview 07
  Scenario Outline: A slug that could address something other than a page returns not found
    When I navigate to "/admin/pages/<slug>/preview"
    Then I should receive a 404 not found response

    Examples:
      | slug           |
      | ..             |
      | ..%2F..%2F.env |
      | .env           |
      | About          |
      | not_a_slug     |

  # Admin page preview 08
  Scenario: A preview cannot be opened while signed out
    Given I am not signed in
    And a draft page exists with slug "about"
    When I navigate to "/admin/pages/about/preview"
    Then I should be redirected to the login page
    And I should not see the draft's content

  # Admin page preview 09
  Scenario: Raw HTML in a previewed page is escaped rather than rendered
    Given a draft page exists with slug "about" whose body contains "<script>alert(1)</script>"
    When I navigate to "/admin/pages/about/preview"
    Then I should see the raw HTML as visible text
    And the script should not be part of the page markup
