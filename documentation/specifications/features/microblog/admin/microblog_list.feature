Feature: Admin microblog list
  In order to manage previously posted short messages
  As a signed-in editor
  I want an overview of every message with quick actions to edit and delete

  Background:
    Given I am signed in as an editor

  # Admin microblog list 01
  Scenario: Every posted message is listed, newest first
    Given the following messages were posted in order:
      | body           |
      | First message  |
      | Second message |
      | Third message  |
    When I open the admin message list
    Then I should see the messages in the following order:
      | Third message  |
      | Second message |
      | First message  |

  # Admin microblog list 02
  Scenario: Each listed message shows when it was posted
    Given a message exists with body "Hello world" posted at "2026-05-01 14:32"
    When I open the admin message list
    Then I should see the posted timestamp "01/05/2026 14:32"

  # Admin microblog list 03
  Scenario: An empty list explains there are no messages yet
    Given no messages exist
    When I open the admin message list
    Then I should see "Nog geen berichten."

  # Admin microblog list 04
  Scenario: Deleting a message
    Given a message exists with body "Goner"
    When I open the admin message list
    And I delete the message "Goner"
    Then the message "Goner" should no longer exist
    And I should see the confirmation "Message deleted."
    And the message "Goner" should no longer appear on the homepage

  # Admin microblog list 05
  Scenario: Reaching the composer and editor from the list
    Given a message exists with body "Welcome"
    When I open the admin message list
    Then I should see a link to post a new message
    And I should see a link to edit the message "Welcome"

  # Admin microblog list 06
  Scenario: The admin message list cannot be reached while signed out
    Given I am not signed in
    When I open the admin message list
    Then I should be redirected to the login page
