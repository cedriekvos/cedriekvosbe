Feature: Post code highlighting
  In order to make code examples easy to scan and understand
  As a visitor reading a blog post
  I want fenced code blocks in the article body to be rendered with syntax highlighting

  Background:
    Given I am a visitor of the site

  # Post code highlighting 01
  Scenario Outline: A fenced code block with a recognized language is rendered with syntax highlighting
    Given a published post exists whose body contains a fenced code block written in "<language>"
    When I navigate to the post's page
    Then the code block should be rendered with syntax highlighting for "<language>"

    Examples:
      | language   |
      | php        |
      | javascript |
      | bash       |

  # Post code highlighting 02
  Scenario: A fenced code block without a language is rendered as plain code
    Given a published post exists whose body contains a fenced code block with no language specified
    When I navigate to the post's page
    Then the code block should be rendered without syntax highlighting
    And the code should still be shown inside a code block

  # Post code highlighting 03
  Scenario: A fenced code block with an unrecognized language is rendered as plain code
    Given a published post exists whose body contains a fenced code block written in "cobol"
    When I navigate to the post's page
    Then the code block should be rendered without syntax highlighting
    And the code should still be shown inside a code block

  # Post code highlighting 04
  Scenario: Inline code spans are never syntax highlighted
    Given a published post exists whose body contains the inline code span "{php}$variable"
    When I navigate to the post's page
    Then the inline code should be rendered as plain code, not as highlighted php

  # Post code highlighting 05
  Scenario Outline: Highlighted code uses a distinct colour theme per site mode
    Given a published post exists whose body contains a fenced code block written in "php"
    And I have switched the site theme to "<mode>"
    When I navigate to the post's page
    Then the code block should be highlighted using the site's designated "<mode>"-mode syntax highlighting theme

    Examples:
      | mode  |
      | light |
      | dark  |

  # Post code highlighting 06
  Scenario: The light-mode and dark-mode syntax highlighting themes are visibly different
    Given a published post exists whose body contains a fenced code block written in "php"
    When I view the post's page with the site theme set to "light"
    And I view the post's page with the site theme set to "dark"
    Then the two renderings should use different syntax highlighting colours

  # Post code highlighting 07
  Scenario: Code highlighting does not extend to the About bio
    Given the About bio contains a fenced code block written in "php"
    When I navigate to the homepage
    Then the code block should be rendered without syntax highlighting
