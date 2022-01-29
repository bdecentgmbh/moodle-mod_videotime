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
    And the following "activities" exist:
      | activity  | name                        | intro                      | course | vimeo_url                   | label_mode | section | enabletabs |
      | videotime | Video Time with information | This video has information | C1     | https://vimeo.com/253989945 | 0          | 1       | 1          |
    And I am on the "Video Time with information" "videotime activity editing" page logged in as "teacher"
    And I set the following fields to these values:
      | Video Time Information tab | 1                           |
      | Information tab content    | A big rabbit                |
    And I press "Save and display"

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
