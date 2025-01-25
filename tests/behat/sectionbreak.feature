@format @format_mintcampus
Feature: Section break
  As a teacher I need to break the mintcampus with a section break.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email          |
      | daisy    | Daisy     | mintcampus     | daisy@mintcampus.com |
    And the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | mintcampus     | GD        | mintcampus    | 10          |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | daisy    | GD     | editingteacher |
    And I am on the "GD" "Course" page logged in as "daisy"

  @javascript
  Scenario: Create a section 4 break
    When I turn editing mode on
    And I edit the section "4"
    And I select "Yes" from the "sectionbreak" singleselect
    And I set the field "Section break heading" to "Section four break"
    And I press "Save changes"
    And I turn editing mode off
    Then I should see "Section four break" in the "#mintcampussectionbreak-4" "css_element"
