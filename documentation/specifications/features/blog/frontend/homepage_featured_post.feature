Feature: Homepage featured post badge
  In order to draw attention to the posts that deserve extra visibility
  As a visitor of the site
  I want the homepage to mark featured posts with an "Uitgelicht" badge

  Background:
    Given I am a visitor of the site

  # Homepage featured post 01
  Scenario: A featured post is marked with the Uitgelicht badge
    Given the following published posts exist:
      | title         | date       | featured |
      | Featured post | 2026-05-01 | yes      |
      | Regular post  | 2026-05-02 | no       |
    When I navigate to "/"
    Then "Featured post" should be marked with the "Uitgelicht" badge
    And "Regular post" should not be marked with the "Uitgelicht" badge

  # Homepage featured post 02
  Scenario: The badge follows the featured flag, not the post's position in the list
    Given the following published posts exist:
      | title          | date       | featured |
      | Newest post    | 2026-06-01 | no       |
      | Older featured | 2026-05-01 | yes      |
    When I navigate to "/"
    Then "Newest post" should not be marked with the "Uitgelicht" badge
    And "Older featured" should be marked with the "Uitgelicht" badge

  # Homepage featured post 03
  Scenario: More than one post can show the badge at the same time
    Given the following published posts exist:
      | title  | date       | featured |
      | Post A | 2026-05-01 | yes      |
      | Post B | 2026-05-02 | yes      |
      | Post C | 2026-05-03 | no       |
    When I navigate to "/"
    Then "Post A" should be marked with the "Uitgelicht" badge
    And "Post B" should be marked with the "Uitgelicht" badge
    And "Post C" should not be marked with the "Uitgelicht" badge

  # Homepage featured post 04
  Scenario: No badge appears when no post is featured
    Given the following published posts exist:
      | title  | date       | featured |
      | Post A | 2026-05-01 | no       |
      | Post B | 2026-05-02 | no       |
    When I navigate to "/"
    Then I should not see "Uitgelicht"
