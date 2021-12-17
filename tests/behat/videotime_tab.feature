@mod @mod_videotime
Feature: Configure videotime tabs
  In order to use a video assignment I need to place information in tabs
  As an teacher
  I need view all tabs

  Background:
    Given the following "courses" exist:
      | shortname | fullname   |
      | C1        | Course 1 |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teacher   |
      | student  | Student   |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |
    And I log in as "teacher"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "Video Time" to section "1" and I fill the form with:
      | Activity name              | Video Time with information |
      | Vimeo URL                  | https://vimeo.com/253989945 |
      | Description                | This video has information  |
      | Enable tab                 | 1                           |
      | Video Time Information tab | 1                           |
      | Information tab content    | A big rabbit                |
    And I turn editing mode off

  @javascript
  Scenario: See information on information tab
    Given I am on the "Video Time with information" "videotime activity" page
    When I follow "Information"
    Then I should see "A big rabbit" in the "region-main" "region"

  @javascript
  Scenario: Do not see information on watch tab
    Given I am on the "Video Time with information" "videotime activity" page
    When I follow "Watch"
    Then I should not see "A big rabbit" in the "region-main" "region"
