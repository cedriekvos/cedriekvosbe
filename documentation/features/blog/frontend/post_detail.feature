Feature: Blog post detail
  In order to read a published article in full and share a link to it
  As a visitor of the site
  I want every published post to have its own page with the complete content

  Background:
    Given I am a visitor of the site

  Scenario: Viewing a published post
    Given a published post exists with:
      | title   | slug    | date       |
      | Welcome | welcome | 2026-05-01 |
    When I navigate to "/blog/welcome"
    Then I should see the post title "Welcome"
    And I should see the publication date "01/05/2026"
    And I should see the article content rendered from Markdown

  Scenario: The page links back to the homepage
    Given a published post exists with slug "welcome"
    When I navigate to "/blog/welcome"
    Then I should see a navigation link back to the homepage "/"

  Scenario: Visiting an unknown post returns not found
    Given no post exists with slug "does-not-exist"
    When I navigate to "/blog/does-not-exist"
    Then I should receive a 404 not found response

  # Post detail 04
  Scenario: The post's excerpt appears as a lead-in before the article content
    Given a published post exists with:
      | title   | slug    | excerpt                       |
      | Welcome | welcome | A short introduction to begin |
    When I navigate to "/blog/welcome"
    Then I should see the excerpt "A short introduction to begin" before the article content

  # Post detail 05
  Scenario: A post without an excerpt shows no excerpt lead-in
    Given a published post exists with:
      | title   | slug    | excerpt |
      | Welcome | welcome |         |
    When I navigate to "/blog/welcome"
    Then I should not see an excerpt

  # Post detail 06
  Scenario Outline: A slug that could address something other than a post returns not found
    When I navigate to "/blog/<slug>"
    Then I should receive a 404 not found response

    Examples:
      | slug            |
      | ..              |
      | ..%2F..%2F.env  |
      | .env            |
      | Welcome         |
      | not_a_slug      |

  # Post detail 07
  Scenario: Raw HTML in a post is escaped rather than rendered
    Given a published post exists with slug "welcome" whose body contains "<script>alert(1)</script>"
    When I navigate to "/blog/welcome"
    Then I should see the raw HTML as visible text
    And the script should not be part of the page markup

  # Post detail 08
  Scenario: An unsafe link in a post is not rendered as a link
    Given a published post exists with slug "welcome" whose body contains a "javascript:" link
    When I navigate to "/blog/welcome"
    Then the page should not contain a "javascript:" link

  # Post detail 09
  Scenario: Article prose containing a long link stays inside the page on a narrow screen
    Given a published post exists with slug "welcome" whose body contains "https://x.com/taylorotwell/status/2077863029874503921"
    When I navigate to "/blog/welcome" on a mobile screen
    Then the article text should wrap within the width of the page
    And the page should not scroll horizontally
