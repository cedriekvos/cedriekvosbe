Feature: Page detail
  In order to read a standalone page in full and share a link to it
  As a visitor of the site
  I want every published page to have its own page with the complete content

  Background:
    Given I am a visitor of the site

  # Page detail 01
  Scenario: Viewing a published page
    Given a published page exists with:
      | title | slug  |
      | About | about |
    When I navigate to "/about"
    Then I should see the page title "About"
    And I should see the page content rendered from Markdown

  # Page detail 02
  Scenario: The page links back to the homepage
    Given a published page exists with slug "about"
    When I navigate to "/about"
    Then I should see a navigation link back to the homepage "/"

  # Page detail 03
  Scenario: Visiting an unknown page returns not found
    Given no page exists with slug "does-not-exist"
    When I navigate to "/does-not-exist"
    Then I should receive a 404 not found response

  # Page detail 04
  Scenario Outline: A slug that could address something other than a page returns not found
    When I navigate to "/<slug>"
    Then I should receive a 404 not found response

    Examples:
      | slug           |
      | ..             |
      | ..%2F..%2F.env |
      | .env           |
      | About          |
      | not_a_slug     |

  # Page detail 05
  Scenario: Raw HTML in a page is escaped rather than rendered
    Given a published page exists with slug "about" whose body contains "<script>alert(1)</script>"
    When I navigate to "/about"
    Then I should see the raw HTML as visible text
    And the script should not be part of the page markup

  # Page detail 06
  Scenario: An unsafe link in a page is not rendered as a link
    Given a published page exists with slug "about" whose body contains a "javascript:" link
    When I navigate to "/about"
    Then the page should not contain a "javascript:" link

  # Page detail 07
  Scenario: Page prose containing a long link stays inside the page on a narrow screen
    Given a published page exists with slug "about" whose body contains "https://x.com/taylorotwell/status/2077863029874503921"
    When I navigate to "/about" on a mobile screen
    Then the page text should wrap within the width of the page
    And the page should not scroll horizontally
