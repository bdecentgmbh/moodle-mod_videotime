@mod @mod_videotime @videotimetab_related @videotimetab_related_display
Feature: Customize videotime related tab label
  In order to create a video assignment I need to customize related tab label
  As an teacher
  I need change related activites tab label

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
      | videotime | Video1 | VideoDesc1 | C1     | https://vimeo.com/347119375 | 0          | 1       | 1          |
    And the following "activities" exist:
      | activity  | name   | intro     | course | content      | section |
      | page      | Page1  | PageDesc1 | C1     | PageContent1 | 1       |
      | page      | Page2  | PageDesc2 | C1     | PageContent2 | 2       |
    And the following config values are set as admin:
      | enabled | 1 | videotimetab_related |
      | default | 1 | videotimetab_related |

  @javascript
  Scenario: Customize related activites tab label
    Given I log in as "teacher"
    And I am on the "Video1" "videotime activity editing" page
    And I set the following fields to these values:
      | Video Time Related activities tab | 1          |
    When I press "Save and display"
    And I follow "Related activities"
    Then I should see "Page1" in the ".videotimetabs" "css_element"
    And I should not see "Page2" in the ".videotimetabs" "css_element"
