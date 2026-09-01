Feature: Homepage post list
  In order to discover the blog's published content
  As a visitor of the site
  I want the homepage to list every published post, newest first

  Background:
    Given I am a visitor of the site

  Scenario: Published posts are listed newest first
    Given the following published posts exist:
      | title       | date       |
      | First post  | 2026-01-15 |
      | Second post | 2026-03-02 |
      | Third post  | 2026-05-20 |
    When I navigate to "/"
    Then I should see the posts in the following order:
      | Third post  |
      | Second post |
      | First post  |

  Scenario: Posts published on the same date are ordered alphabetically by title
    Given the following published posts exist:
      | title  | date       |
      | Cherry | 2026-05-10 |
      | Apple  | 2026-05-10 |
      | Banana | 2026-05-10 |
    When I navigate to "/"
    Then I should see the posts in the following order:
      | Apple  |
      | Banana |
      | Cherry |

  Scenario: Each listed post shows its title, date, and excerpt
    Given a published post exists with:
      | title   | date       | excerpt                       |
      | Welcome | 2026-05-01 | A short introduction to begin |
    When I navigate to "/"
    Then I should see the post title "Welcome"
    And I should see the publication date "01/05/2026"
    And I should see the excerpt "A short introduction to begin"
    And the post title should link to "/blog/welcome"

  Scenario Outline: The publication date is displayed as day/month/year
    Given a published post exists with:
      | title   | date          |
      | Example | <stored_date> |
    When I navigate to "/"
    Then I should see the publication date "<displayed_date>"

    Examples:
      | stored_date | displayed_date |
      | 2026-05-01  | 01/05/2026     |
      | 2026-11-03  | 03/11/2026     |
      | 2026-12-25  | 25/12/2026     |

  Scenario: Draft posts are hidden from the homepage
    Given the following posts exist:
      | title            | status    |
      | Public update    | published |
      | Work in progress | draft     |
    When I navigate to "/"
    Then I should see "Public update"
    And I should not see "Work in progress"
