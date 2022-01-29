@mod @mod_videotime @videotimetab_block
Feature: Configure videotime block tab
  In order to create a video assignment I need to edit blocks in block tab
  As an teacher
  I need edit blocks in block tab

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
      | activity  | name   | intro      | course | vimeo_url                   | label_mode | section | enabletabs |
      | videotime | Video1 | VideoDesc1 | C1     | https://vimeo.com/253989945 | 0          | 1       | 1          |
    And the following config values are set as admin:
      | enabled | 1 | videotimetab_block |
      | default | 1 | videotimetab_block |

  @javascript
  Scenario: Add a comments block
    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I am on the "Video1" "videotime activity editing" page
    And I set the following fields to these values:
      | Video Time Block tab  | 1   |
    And I press "Save and display"
    When I follow "Blocks"
    And I set the field "Add a block" to "Comments"
    And I follow "Blocks"
    Then I should see "Comments" in the "region-main" "region"
    When I follow "Watch"
    Then I should not see "Comments" in the "region-main" "region"

  @javascript
  Scenario: Disable block tab
    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I am on the "Video1" "videotime activity editing" page
    And I set the following fields to these values:
      | Video Time Block tab       | 0                           |
    When I press "Save and display"
    Then I should not see "Blocks" in the "region-main" "region"
