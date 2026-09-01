Feature: Admin scratchpad
  In order to jot down and revisit personal notes while managing the blog
  As a signed-in editor
  I want a single screen to edit free-form Markdown notes, just like editing a post's body

  Background:
    Given I am signed in as an editor

  # Scratchpad-1
  Scenario: The editor is pre-filled with the stored scratchpad content
    Given the scratchpad content is "Remember to check backlinks."
    When I open the scratchpad
    Then the content field should contain "Remember to check backlinks."

  # Scratchpad-2
  Scenario: Updating the scratchpad content
    Given the scratchpad content is "Old note."
    When I open the scratchpad
    And I change the content to "New note."
    And I save
    Then the stored scratchpad content should be "New note."
    And I should see the confirmation "Scratchpad updated."
    And I should be returned to the post list

  # Scratchpad-3
  Scenario Outline: Any content can be saved, including empty
    When I open the scratchpad
    And I set the content to "<content>"
    And I save
    Then the stored scratchpad content should be "<content>"
    And I should see the confirmation "Scratchpad updated."

    Examples:
      | content          |
      | Some notes here. |
      |                  |

  # Scratchpad-4
  Scenario: The content preserves Markdown exactly as written
    When I open the scratchpad
    And I change the content to "Line one\n\n- point **a**\n- point b"
    And I save
    Then the stored scratchpad content should be "Line one\n\n- point **a**\n- point b"

  # Scratchpad-5
  Scenario: The scratchpad is reachable from the admin header
    When I open the admin post list
    Then I should see a link to open the scratchpad

  # Scratchpad-6
  Scenario: The scratchpad cannot be reached while signed out
    Given I am not signed in
    When I open the scratchpad
    Then I should be redirected to the login page
