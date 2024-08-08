@mod @mod_videotime @videotimeplugin_videojs
Feature: Add file to Video Time
  In a video assignment
  As an teacher
  I need add videos to a videotime activity

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
      | activity  | name   | intro      | course | vimeo_url                   | label_mode | section |
      | videotime | Video1 | VideoDesc1 | C1     | https://vimeo.com/347119375 | 0          | 1       |

  @javascript @_file_upload
  Scenario: Upload MP4
    Given I am logged in as "teacher"
    And I am on the "Video1" "videotime activity editing" page
    And I upload "mod/videotime/plugin/videojs/tests/fixtures/test.mp4" file to "Media file" filemanager
    When I press "Save and display"
    Then I should not see "Editing"

  @javascript @_file_upload
  Scenario: Upload WebM
    Given I am logged in as "teacher"
    And I am on the "Video1" "videotime activity editing" page
    And I upload "mod/videotime/plugin/videojs/tests/fixtures/test.webm" file to "Media file" filemanager
    When I press "Save and display"
    Then I should not see "Editing"
