@mod @mod_videotime @videotimeplugin_vimeo
Feature: Configure videotime tabs
  In use a video assignment I need to control settings
  As an admin
  I need to adjust player options

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
      | activity  | name                        | course | vimeo_url                   | label_mode | section | controls |
      | videotime | Video Time with controls    | C1     | https://vimeo.com/253989945 | 0          | 1       | 1        |
      | videotime | Video Time with no controls | C1     | https://vimeo.com/253989945 | 0          | 1       | 0        |

  @javascript
  Scenario: Controls are available
    When I am on the "Video Time with controls" "mod_videotime > Embed options" page logged in as "teacher"
    And I set the following fields to these values:
      | controls    | 1 |
    And I press "Save changes"
    And I wait "3" seconds
    And I switch to "" class iframe
    Then "Play" "button" should be visible

  @javascript
  Scenario: Controls are available
    When I am on the "Video Time with no controls" "mod_videotime > Embed options" page logged in as "teacher"
    And I set the following fields to these values:
      | controls    | 0 |
    And I press "Save changes"
    And I wait "3" seconds
    And I switch to "" class iframe
    Then "Play" "button" should not be visible
