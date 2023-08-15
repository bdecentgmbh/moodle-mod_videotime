@mod @mod_videotime @mod_videotime_notes
Feature: Video Time notes
  In order to do a video assignment
  As a student
  I need read notes

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
        | activity  | name                          | intro                      | course | vimeo_url                   | label_mode | section | enabletabs | video_description  | show_description_in_player |
        | videotime | Video Time without tabs       | This video has information | C1     | https://vimeo.com/347119375 | 0          | 1       | 0          | The world is round | 0                          |
        | videotime | Video Time with tabs          | This video has information | C1     | https://vimeo.com/347119375 | 0          | 1       | 1          | The world is round | 1                          |
        | videotime | Video Time with info          | This video has information | C1     | https://vimeo.com/347119375 | 0          | 1       | 0          | The world is round | 1                          |
        | videotime | Video Time with no info       | This video has information | C1     | https://vimeo.com/347119375 | 0          | 1       | 1          | The world is round | 0                          |
    And I am on the "Video Time with tabs" "videotime activity editing" page logged in as "teacher"
    And I set the following fields to these values:
      | Video Time Information tab | 1                           |
      | Information tab content    | A big rabbit                |
    And I press "Save and display"

  @javascript
  Scenario: See notes on activity without tabs
    When I am on the "Video Time without tabs" "videotime activity" page logged in as "student"
    Then I should see "The world is round" in the "region-main" "region"

  @javascript
  Scenario: See notes on activity with tabs
    When I am on the "Video Time with tabs" "videotime activity" page logged in as "student"
    Then I should see "The world is round" in the "region-main" "region"

  @javascript
  Scenario: See information when set and tabs
    When I am on the "Video Time with tabs" "videotime activity" page logged in as "student"
    Then I should see "This video has information" in the "region-main" "region"

  @javascript
  Scenario: See information when set and no tabs
    When I am on the "Video Time with info" "videotime activity" page logged in as "student"
    Then I should see "This video has information" in the "region-main" "region"

  @javascript
  Scenario: See information when not set and tabs
    When I am on the "Video Time with no info" "videotime activity" page logged in as "student"
    Then I should not see "This video has information" in the "region-main" "region"

  @javascript
  Scenario: See no information when set
    When I am on the "Video Time without tabs" "videotime activity" page logged in as "student"
    Then I should not see "This video has information" in the "region-main" "region"
