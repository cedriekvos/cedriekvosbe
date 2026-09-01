Feature: Post read time
  In order to gauge how much time it will take to read a post before committing to it
  As a visitor of the site
  I want every post to show an estimated read time next to its publication date

  Background:
    Given I am a visitor of the site

  # Post read time 01
  Scenario: Read time appears next to the date on the homepage post list
    Given a published post exists with a body of 50 words
    When I navigate to "/"
    Then I should see "1 min read" next to the post's publication date

  # Post read time 02
  Scenario: Read time appears next to the date on the post detail page
    Given a published post exists with slug "welcome" and a body of 400 words
    When I navigate to "/blog/welcome"
    Then I should see "2 min read" next to the post's publication date

  # Post read time 03
  Scenario Outline: Read time is the word count divided by 200 words per minute, rounded up
    Given a published post exists with a body of <word_count> words
    When I navigate to "/"
    Then I should see "<read_time> min read"

    Examples:
      | word_count | read_time |
      | 1          | 1         |
      | 199        | 1         |
      | 200        | 1         |
      | 201        | 2         |
      | 400        | 2         |
      | 401        | 3         |
      | 1000       | 5         |

  # Post read time 04
  Scenario: A post without any body text still shows a minimum read time
    Given a published post exists with an empty body
    When I navigate to "/"
    Then I should see "1 min read"

  # Post read time 05
  Scenario: Read time is based on visible text, not markdown or HTML syntax
    Given a published post whose body is an image with 250 words of alt text followed by the text "Short post"
    When I navigate to "/"
    Then I should see "1 min read"
