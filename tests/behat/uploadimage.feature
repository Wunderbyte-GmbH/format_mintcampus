@format @format_mintcampus
Feature: Image upload
  As a teacher I need to upload an image to a section.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email          |
      | daisy    | Daisy     | mintcampus     | daisy@mintcampus.com |
    And the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | mintcampus     | GD        | mintcampus    | 5           |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | daisy    | GD     | editingteacher |
    And I am on the "GD" "Course" page logged in as "daisy"

  @_file_upload @javascript
  Scenario: Upload an image to section 2
    When I turn editing mode on
    And I edit the section "2"
    And I upload "course/format/mintcampus/tests/fixtures/Duckling.jpg" file to "Section image" filemanager
    And I set the field "Image alt text" to "Duckling"
    And I press "Save changes"
    And I turn editing mode off
    Then "//img[contains(@src, 'Duckling.jpg')]" "xpath_element" should exist in the "#section-2 .mintcampus-image" "css_element"
    And "//img[contains(@alt, 'Duckling')]" "xpath_element" should exist in the "#section-2 .mintcampus-image" "css_element"
