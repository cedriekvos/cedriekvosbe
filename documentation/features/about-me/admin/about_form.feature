Feature: Admin about-me editor
  In order to keep my introduction on the homepage current
  As a signed-in editor
  I want a single screen that edits the about-me heading and bio, just like I edit a post

  Background:
    Given I am signed in as an editor

  # AboutForm-1
  Scenario: The editor is pre-filled with the stored about content
    Given the about content is:
      | heading  | bio                   |
      | About me | I write about **Go**. |
    When I open the about editor
    Then the heading field should contain "About me"
    And the bio field should contain "I write about **Go**."

  # AboutForm-2
  Scenario: Updating the about content
    Given the about content is:
      | heading  | bio      |
      | About me | Old bio. |
    When I open the about editor
    And I change the heading to "Over mij"
    And I change the bio to "Nieuwe bio."
    And I save
    Then the stored about content should be:
      | heading  | bio         |
      | Over mij | Nieuwe bio. |
    And I should see the confirmation "About updated."
    And I should be returned to the post list

  # AboutForm-3
  Scenario Outline: Any combination of heading and bio can be saved, including an empty about
    When I open the about editor
    And I set the heading to "<heading>" and the bio to "<bio>"
    And I save
    Then the about content should be stored with heading "<heading>" and bio "<bio>"
    And I should see the confirmation "About updated."

    Examples:
      | heading  | bio             |
      | About me | I build things. |
      | About me |                 |
      |          | I build things. |
      |          |                 |

  # AboutForm-4
  Scenario: The bio preserves Markdown exactly as written
    When I open the about editor
    And I change the bio to "Line one\n\n- point **a**\n- point b"
    And I save
    Then the stored bio should be "Line one\n\n- point **a**\n- point b"

  # AboutForm-5
  Scenario: The about editor is reachable from the admin header
    When I open the admin post list
    Then I should see a link to edit the about content

  # AboutForm-6
  Scenario: The about editor cannot be reached while signed out
    Given I am not signed in
    When I open the about editor
    Then I should be redirected to the login page
