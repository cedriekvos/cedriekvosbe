Feature: Header LinkedIn link
  In order to let visitors find my professional profile and get in touch
  As a visitor of the site
  I want the site header to show a link to my LinkedIn profile next to the GitHub link

  Background:
    Given I am a visitor of the site

  # Header LinkedIn link 01
  Scenario Outline: The header shows a LinkedIn link on every public page
    When I navigate to "<page>"
    Then the header should show a link pointing to "https://www.linkedin.com/in/cedriekvos"

    Examples:
      | page          |
      | /             |
      | /blog/welcome |

  # Header LinkedIn link 02
  Scenario: The LinkedIn link is placed directly next to the GitHub link
    When I navigate to "/"
    Then the header's LinkedIn link should be placed directly next to the header's GitHub link
    And the header's GitHub link should still point to "https://github.com/cedriekvos"

  # Header LinkedIn link 03
  Scenario: The LinkedIn link opens in a new tab without exposing the site to tab-nabbing
    When I navigate to "/"
    Then the header's LinkedIn link should open in a new tab
    And the header's LinkedIn link should carry the following rel attributes:
      | noopener |
      | noreferrer |

  # Header LinkedIn link 04
  Scenario: The LinkedIn link has an accessible label for screen reader users
    When I navigate to "/"
    Then the header's LinkedIn link should have the accessible name "LinkedIn"

  # Header LinkedIn link 05
  Scenario Outline: The LinkedIn link shows the official logo variant that suits the active theme
    Given I have switched the site theme to "<mode>"
    When I navigate to "/"
    Then the header's LinkedIn link should be visible
    And the header's LinkedIn link should display the "<shown>" logo
    And the header's LinkedIn link should not display the "<hidden>" logo

    Examples:
      | mode  | shown | hidden |
      | light | black | white  |
      | dark  | white | black  |

  # Header LinkedIn link 06
  Scenario Outline: Both official LinkedIn logo variants load successfully
    When I navigate to "/"
    Then the header's LinkedIn "<variant>" logo image should have loaded without error

    Examples:
      | variant |
      | black   |
      | white   |
