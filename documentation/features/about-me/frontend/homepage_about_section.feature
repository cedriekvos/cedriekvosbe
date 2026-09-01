Feature: Homepage about-me section
  In order to know whose blog I am reading the moment I arrive
  As a visitor of the site
  I want a short about-me introduction at the top of the homepage, above the posts

  Background:
    Given I am a visitor of the site

  Scenario: The about-me section is shown above the post list
    Given the about-me section is configured with:
      | heading  | bio              |
      | About me | Hi, I'm Cedriek. |
    And a published post exists with title "Welcome"
    When I navigate to "/"
    Then I should see the about-me heading "About me"
    And I should see the about-me bio "Hi, I'm Cedriek."
    And the about-me section should appear above the post "Welcome"

  Scenario: The bio is rendered from Markdown
    Given the about-me section is configured with bio "I write about **software**."
    When I navigate to "/"
    Then the about-me bio should render "software" in bold

  Scenario Outline: The section is shown whenever the heading or the bio has content
    Given the about-me section is configured with:
      | heading   | bio   |
      | <heading> | <bio> |
    When I navigate to "/"
    Then the about-me section should be <visibility>

    Examples:
      | heading  | bio             | visibility |
      | About me | I build things. | visible    |
      | About me |                 | visible    |
      |          | I build things. | visible    |
      |          |                 | hidden     |

  Scenario: The section is shown even when no posts are published
    Given the about-me section is configured with heading "About me"
    And no published posts exist
    When I navigate to "/"
    Then I should see the about-me heading "About me"
    And I should see "no posts yet"
