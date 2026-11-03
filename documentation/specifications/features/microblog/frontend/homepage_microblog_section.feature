Feature: Homepage microblog section
  In order to see the site owner's short updates at a glance
  As a visitor of the site
  I want the homepage to show a dedicated section listing every short message, newest first

  Background:
    Given I am a visitor of the site

  # Homepage microblog section 01
  Scenario: Messages are listed newest first in their own section
    Given the following messages were posted in order:
      | body           |
      | First message  |
      | Second message |
      | Third message  |
    When I navigate to "/"
    Then I should see a "tail -n 100 messages.log" section
    And within that section I should see the messages in the following order:
      | Third message  |
      | Second message |
      | First message  |

  # Homepage microblog section 02
  Scenario: Each message shows its full body text and posted date
    Given a message exists with body "Just shipped a new feature!" posted at "2026-05-01 14:32"
    When I navigate to "/"
    Then I should see the message text "Just shipped a new feature!"
    And I should see the posted timestamp "01/05/2026 14:32"

  # Homepage microblog section 03
  Scenario: The messages section is separate from the blog posts list
    Given a published post exists with title "Big announcement"
    And a message exists with body "Small update"
    When I navigate to "/"
    Then I should see "Big announcement" in the blog posts list
    And I should see "Small update" in the "Short messages" section
    And "Small update" should not appear in the blog posts list

  # Homepage microblog section 04
  Scenario: No messages have been posted yet
    Given no messages exist
    When I navigate to "/"
    Then I should see "Nog geen berichten." within the "Short messages" section

  # Homepage microblog section 05
  Scenario: The messages section appears even when no blog posts exist
    Given no published posts exist
    And a message exists with body "Still here"
    When I navigate to "/"
    Then I should see "Still here" in the "Short messages" section

  # Homepage microblog section 06
  Scenario: A message containing a long link stays inside the section on a narrow screen
    Given a message exists with body "Bronnen: https://x.com/taylorotwell/status/2077863029874503921"
    When I navigate to "/" on a mobile screen
    Then the message text should wrap within the width of the section
    And the page should not scroll horizontally
