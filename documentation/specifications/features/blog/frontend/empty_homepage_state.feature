Feature: Empty homepage state
  In order to understand that the blog has not published anything yet
  As a visitor of the site
  I want the homepage to tell me clearly when there are no posts

  Scenario: No posts have been published
    Given I am a visitor of the site
    And no published posts exist
    When I navigate to "/"
    Then I should see "no posts yet"
