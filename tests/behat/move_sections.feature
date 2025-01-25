@format @format_mintcampus
Feature: Sections can be moved - adapted from core test of the same name.
  In order to rearrange my course contents
  As a teacher
  I need to move sections up and down

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | daisy    | Daisy     | mintcampus     | daisy@mintcampus.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections |
      | mintcampus     | GD        | mintcampus   | 0             | 5           |
    And the following "course enrolments" exist:
      | user  | course | role           |
      | daisy | GD     | editingteacher |
    And the following "activities" exist:
      | activity | name               | intro                       | course | idnumber | section |
      | forum    | Test forum name    | Test forum name description | GD     | forum1   | 1       |
    And I log in as "daisy"
    And I am on "mintcampus" course homepage with editing mode on

  Scenario: Move up and down a section with Javascript disabled in a single page course
    When I move down section "1"
    Then I should see "Test forum name" in the "Section 2" "section"
    And I move up section "2"
    And I should see "Test forum name" in the "Section 1" "section"

  @javascript
  Scenario: Move section with javascript
    When I open section "1" edit menu
    And I click on "Move" "link" in the "Section 1" "section"
    And I click on "Section 3" "link" in the ".modal-body" "css_element"
    Then I should see "Test forum name" in the "Section 3" "section"

  @_file_upload @javascript
  Scenario: Move section with an image.
    When I edit the section "2"
    And I upload "course/format/mintcampus/tests/fixtures/Duckling.jpg" file to "Section image" filemanager
    And I set the field "Image alt text" to "Duckling"
    And I press "Save changes"
    And I open section "2" edit menu
    And I click on "Move" "link" in the "Section 2" "section"
    And I click on "Section 3" "link" in the ".modal-body" "css_element"
    And I turn editing mode off
    Then "//img[contains(@src, 'Duckling.jpg')]" "xpath_element" should exist in the "#section-3 .mintcampus-image" "css_element"
    And "//img[contains(@alt, 'Duckling')]" "xpath_element" should exist in the "#section-3 .mintcampus-image" "css_element"
