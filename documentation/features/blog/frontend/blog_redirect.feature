Feature: Blog redirect
  In order to consolidate site content under a single entry point
  As a visitor of the site
  I want requests to /blog to send me to the homepage

  Scenario: Visiting /blog redirects to the homepage
    Given I am a visitor of the site
    When I navigate to "/blog"
    Then I should be redirected to "/" with a 302 statuscode
