Feature: Theme switcher dropdown
  In order to pick a theme without a cramped icon-only control
  As a visitor of the site
  I want to choose light, dark, or auto from a dropdown menu in the header

  Background:
    Given I am a visitor of the site

  # Theme switcher 01
  Scenario Outline: The closed switcher shows only the icon for the active mode
    Given I have switched the site theme to "<mode>"
    When I navigate to "/"
    Then the theme switcher button should show the icon for "<label>"
    And the theme switcher button should not show a visible text label

    Examples:
      | mode  | label |
      | light | Light |
      | dark  | Dark  |
      | auto  | Auto  |

  # Theme switcher 01b
  Scenario: The closed switcher icon is the same size as the GitHub icon
    When I navigate to "/"
    Then the theme switcher button's icon should be the same size as the header's GitHub icon

  # Theme switcher 02
  Scenario: Opening the switcher lists all three modes in a fixed order
    When I open the theme switcher menu
    Then the theme switcher menu should show the options "Light", "Dark", "Auto" in that order, ignoring the checkmark on whichever option is active

  # Theme switcher 03
  Scenario Outline: The active mode shows a visible checkmark inside the open menu
    Given I have switched the site theme to "<mode>"
    When I open the theme switcher menu
    Then only the "<label>" option should show a checkmark
    And only the "<label>" option should be marked as the current selection via aria-checked

    Examples:
      | mode  | label |
      | light | Light |
      | dark  | Dark  |
      | auto  | Auto  |

  # Theme switcher 04
  Scenario Outline: Selecting a mode from the menu applies it and closes the menu
    Given I have switched the site theme to "<from>"
    And I have opened the theme switcher menu
    When I select "<to>" from the theme switcher menu
    Then the site theme should be "<to>"
    And the theme switcher menu should be closed
    And the theme switcher button should show the icon for "<to>"

    Examples:
      | from  | to    |
      | light | dark  |
      | dark  | auto  |
      | auto  | light |

  # Theme switcher 05
  Scenario: The chosen mode is remembered on the next visit
    Given I have switched the site theme to "dark"
    When I navigate to "/" again
    Then the theme switcher button should show the icon for "Dark"

  # Theme switcher 06
  Scenario: Clicking outside the open menu closes it without changing the theme
    Given I have switched the site theme to "light"
    And I have opened the theme switcher menu
    When I click outside the theme switcher
    Then the theme switcher menu should be closed
    And the site theme should still be "light"

  # Theme switcher 07
  Scenario: Pressing Escape closes the menu and returns focus to the switcher button
    Given I have opened the theme switcher menu
    When I press the "Escape" key
    Then the theme switcher menu should be closed
    And the theme switcher button should have keyboard focus

  # Theme switcher 08
  Scenario: The switcher button opens the menu from the keyboard with focus on the active option
    Given I have switched the site theme to "dark"
    And the theme switcher button has keyboard focus
    When I press the "Enter" key
    Then the theme switcher menu should be open
    And the "Dark" option should have keyboard focus

  # Theme switcher 09
  Scenario: Arrow keys move focus between the menu options
    Given I have opened the theme switcher menu with keyboard focus on "Light"
    When I press the "ArrowDown" key
    Then the "Dark" option should have keyboard focus
    When I press the "ArrowDown" key
    Then the "Auto" option should have keyboard focus

  # Theme switcher 10
  Scenario: Selecting a focused option from the keyboard applies it and closes the menu
    Given I have opened the theme switcher menu with keyboard focus on "Auto"
    When I press the "Enter" key
    Then the site theme should be "auto"
    And the theme switcher menu should be closed
    And the theme switcher button should have keyboard focus

  # Theme switcher 11
  Scenario Outline: The switcher button has an accessible name and expanded state
    Given the theme switcher menu is "<state>"
    Then the theme switcher button should have the accessible name "Theme"
    And the theme switcher button should report its expanded state as "<expanded>"

    Examples:
      | state  | expanded |
      | closed | false    |
      | open   | true     |

  # Theme switcher 12
  Scenario Outline: The switcher is visible and usable in both light and dark mode
    Given I have switched the site theme to "<mode>"
    When I navigate to "/"
    Then the theme switcher button should be visible

    Examples:
      | mode  |
      | light |
      | dark  |
