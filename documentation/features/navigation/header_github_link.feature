Feature: Header GitHub link
  In order to let visitors find my open-source work and profile
  As a visitor of the site
  I want the site header to show a link to my GitHub profile

  Background:
    Given I am a visitor of the site

  # Header GitHub link 01
  Scenario Outline: The header shows a GitHub link on every public page
    When I navigate to "<page>"
    Then the header should show a link pointing to "https://github.com/cedriekvos"

    Examples:
      | page          |
      | /             |
      | /blog/welcome |

  # Header GitHub link 02
  Scenario: The GitHub link opens in a new tab without exposing the site to tab-nabbing
    When I navigate to "/"
    Then the header's GitHub link should open in a new tab
    And the header's GitHub link should carry the following rel attributes:
      | noopener |
      | noreferrer |

  # Header GitHub link 03
  Scenario: The GitHub link has an accessible label for screen reader users
    When I navigate to "/"
    Then the header's GitHub link should have the accessible name "GitHub"

  # Header GitHub link 04
  Scenario Outline: The GitHub link is visible in both light and dark mode
    Given I have switched the site theme to "<mode>"
    When I navigate to "/"
    Then the header's GitHub link should be visible

    Examples:
      | mode  |
      | light |
      | dark  |
