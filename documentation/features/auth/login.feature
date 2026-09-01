Feature: Authentication
  In order to manage blog content securely
  As an editor
  I want to sign in and out, with signing in as the only available account action

  Scenario: The login screen is available
    Given I am not signed in
    When I navigate to "/login"
    Then I should see the login form

  Scenario: Signing in with valid credentials
    Given an editor account exists
    When I submit the login form with the correct email and password
    Then I should be signed in
    And I should be redirected to the admin area

  Scenario: An incorrect password is rejected
    Given an editor account exists
    When I submit the login form with the wrong password
    Then I should remain signed out

  Scenario: Email and password are required
    When I submit the login form without an email address
    Then I should see a validation error for the email field

  Scenario: Signing out
    Given I am signed in as an editor
    When I sign out
    Then I should be signed out
    And I should be redirected to the homepage "/"

  Scenario: Repeated failed attempts are rate limited
    Given an editor account exists
    When I submit the login form with the wrong password six times in a row
    Then the final attempt should be rejected with a message telling me how many seconds to wait

  Scenario: The admin area cannot be reached while signed out
    Given I am not signed in
    When I navigate to "/admin"
    Then I should be redirected to the login page

  Scenario: There is no self-service registration or password reset
    Given I am not signed in
    When I navigate to "/register"
    Then I should receive a 404 not found response
