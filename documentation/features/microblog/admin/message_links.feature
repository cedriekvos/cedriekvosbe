Feature: Message links in the admin
  In order to check that a link I posted actually works
  As a signed-in editor
  I want the admin message list to render bare URLs as clickable links the same way the homepage does

  Background:
    Given I am signed in as an editor

  # Message links in the admin 01
  Scenario: A bare URL in the message list is rendered as a clickable link
    Given a message exists with body "Bronnen: https://php.net/manual"
    When I open the message list
    Then the message should show a link with the text "https://php.net/manual" pointing to "https://php.net/manual"
    And the text "Bronnen:" should be shown outside of any link

  # Message links in the admin 02
  Scenario: A message link does not replace the row's own edit and delete controls
    Given a message exists with body "Zie https://php.net"
    When I open the message list
    Then I should see the "[edit]" control for that message
    And I should see the "[delete]" control for that message

  # Message links in the admin 03
  Scenario: The composer shows the raw body, never a rendered link
    Given a message exists with body "Zie https://php.net"
    When I open the editor for that message
    Then the body field should contain the text "Zie https://php.net"
    And the body field should show no link

  # Message links in the admin 04
  Scenario Outline: The 280 character limit still counts the raw body, URL characters included
    When I compose a message of <length> characters whose body ends in a URL
    And I post it
    Then <outcome>

    Examples:
      | length | outcome                                              |
      | 280    | the message should be posted                          |
      | 281    | I should see a validation error for the message body |
