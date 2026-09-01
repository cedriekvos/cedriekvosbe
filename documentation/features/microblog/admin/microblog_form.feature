Feature: Microblog composer
  In order to publish short updates
  As a signed-in editor
  I want a simple form to post a new short message or edit an existing one

  Background:
    Given I am signed in as an editor

  # Microblog composer 01
  Scenario: Posting a message makes it immediately visible
    When I compose a message with the body "Just shipped a new feature!"
    And I post it
    Then I should see the confirmation "Message posted."
    And the message "Just shipped a new feature!" should appear on the homepage

  # Microblog composer 02
  Scenario: A message body cannot be empty
    When I compose a message with an empty body
    And I post it
    Then I should see a validation error for the message body

  # Microblog composer 03
  Scenario Outline: The message body cannot exceed 280 characters
    When I compose a message with a body of <length> characters
    And I post it
    Then <outcome>

    Examples:
      | length | outcome                                               |
      | 280    | the message should be posted                          |
      | 281    | I should see a validation error for the message body |

  # Microblog composer 04
  Scenario: Editing an existing message updates its content
    Given a message exists with body "Origional typo"
    When I open the editor for the message "Origional typo"
    And I change the body to "Original, fixed"
    And I save
    Then I should see the confirmation "Message updated."
    And the message "Original, fixed" should appear on the homepage
    And the message "Origional typo" should no longer appear on the homepage

  # Microblog composer 05
  Scenario: Editing a message does not change its position in the newest-first order
    Given the following messages were posted in order:
      | body            |
      | First message   |
      | Second message  |
    When I open the editor for the message "First message"
    And I change the body to "First message, edited"
    And I save
    And I navigate to "/"
    Then I should see the messages in the following order:
      | Second message         |
      | First message, edited |

  # Microblog composer 06
  Scenario: Opening the editor for a message that does not exist returns not found
    When I open the editor for a message that does not exist
    Then I should receive a 404 not found response

  # Microblog composer 07
  Scenario Outline: The message composer cannot be reached while signed out
    Given I am not signed in
    When I open <screen>
    Then I should be redirected to the login page

    Examples:
      | screen                          |
      | the message composer            |
      | the editor for an existing message |
