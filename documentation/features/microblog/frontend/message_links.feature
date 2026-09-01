Feature: Message links
  In order to follow a source the site owner referred to in a short update
  As a visitor of the site
  I want bare URLs inside a message body to be rendered as clickable links

  Background:
    Given I am a visitor of the site

  # Message links 01
  Scenario Outline: A bare URL is rendered as a link showing the URL exactly as written
    Given a message exists with body "<body>"
    When I navigate to "/"
    Then the message should show a link with the text "<link text>" pointing to "<href>"

    Examples:
      | body                     | link text                | href                     |
      | https://php.net/manual   | https://php.net/manual   | https://php.net/manual   |
      | http://example.test/page | http://example.test/page | http://example.test/page |
      | www.php.net              | www.php.net              | https://www.php.net      |

  # Message links 02
  Scenario: Only the URL becomes a link; the surrounding words stay plain text
    Given a message exists with body "Bronnen: https://php.net/manual echt de moeite"
    When I navigate to "/"
    Then the message should show a link with the text "https://php.net/manual"
    And the text "Bronnen:" should be shown outside of any link
    And the text "echt de moeite" should be shown outside of any link

  # Message links 03
  Scenario: Every URL in a message containing several URLs becomes its own link
    Given a message exists with body "Zie https://php.net en https://laravel.com voor meer"
    When I navigate to "/"
    Then the message should show 2 links
    And the message should show a link pointing to "https://php.net"
    And the message should show a link pointing to "https://laravel.com"

  # Message links 04
  Scenario Outline: Trailing punctuation after a URL is not part of the link
    Given a message exists with body "Zie https://php.net/manual<punctuation>"
    When I navigate to "/"
    Then the message should show a link with the text "https://php.net/manual"
    And the text "<punctuation>" should be shown outside of any link

    Examples:
      | punctuation |
      | .           |
      | ,           |
      | !           |
      | ?           |

  # Message links 05
  Scenario Outline: Markdown formatting in a message body is shown literally, never rendered
    Given a message exists with body "<body>"
    When I navigate to "/"
    Then the message should show the text "<body>" exactly as written
    And the message should contain no rendered Markdown element

    Examples:
      | body           |
      | **niet vet**   |
      | _niet cursief_ |
      | # geen kop     |
      | `geen code`    |
      | - geen lijst   |

  # Message links 06
  Scenario: Markdown link syntax is not honoured, but the URL inside it is still linked
    Given a message exists with body "Zie [de docs](https://php.net) voor meer"
    When I navigate to "/"
    Then the message should show a link with the text "https://php.net" pointing to "https://php.net"
    And the text "[de docs](" should be shown outside of any link

  # Message links 07
  Scenario Outline: Text that is not an http, https or www URL is never turned into a link
    Given a message exists with body "<body>"
    When I navigate to "/"
    Then the message should show no links
    And the message should show the text "<body>" exactly as written

    Examples:
      | body                        |
      | javascript:alert(1)         |
      | ftp://files.example.test/x  |
      | mailto:cedriek@example.test |
      | cedriek@example.test        |
      | example.test/pagina         |

  # Message links 08
  Scenario: A link to an external site opens in a new tab
    Given a message exists with body "Zie https://php.net/manual"
    When I navigate to "/"
    Then the link should open in a new tab
    And the link should carry the following rel attributes:
      | noopener   |
      | noreferrer |

  # Message links 09
  Scenario: A link to this site itself opens in the same tab
    Given a message exists with body containing a URL on this site's own host
    When I navigate to "/"
    Then the message should show that URL as a link
    And the link should not open in a new tab

  # Message links 10
  Scenario: Raw HTML in a message body is shown as text, not rendered
    Given a message exists with body "<script>alert(1)</script> en <b>vet</b>"
    When I navigate to "/"
    Then the message should show the text "<script>alert(1)</script> en <b>vet</b>" exactly as written
    And the raw HTML should be escaped rather than added to the page

  # Message links 11
  Scenario: A message without a URL is rendered exactly as before
    Given a message exists with body "Just shipped a new feature!"
    When I navigate to "/"
    Then the message should show the text "Just shipped a new feature!" exactly as written
    And the message should show no links

  # Message links 12
  Scenario: A long link stays inside the section on a narrow screen
    Given a message exists with body "Bronnen: https://x.com/taylorotwell/status/2077863029874503921"
    When I navigate to "/" on a mobile screen
    Then the link text should wrap within the width of the section
    And the page should not scroll horizontally
