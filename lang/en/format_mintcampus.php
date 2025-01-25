<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * mintcampus Format.
 *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in version.php.
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['topic'] = 'Section';
$string['topic0'] = 'General';
$string['module'] = 'Module';

// Moodle 2.0 Enhancement - Moodle Tracker MDL-15252, MDL-21693 & MDL-22056 - http://docs.moodle.org/en/Development:Languages.
$string['sectionname'] = 'Section';
$string['pluginname'] = 'Format mintcampus';
$string['section0name'] = 'General';

// MDL-26105.
$string['page-course-view-mintcampus'] = 'Any course main page in the mintcampus format';
$string['page-course-view-mintcampus-x'] = 'Any course page in the mintcampus format';

// section -> modul (MINT requirement)
$string['addsection'] = 'Add modul';
$string['hidefromothers'] = 'Hide modul'; // No longer used kept for legacy versions.
$string['showfromothers'] = 'Show modul'; // No longer used kept for legacy versions.
$string['currentsection'] = 'This modul'; // No longer used kept for legacy versions.
$string['markedthissection'] = 'This modul is highlighted as the current modul';
$string['markthissection'] = 'Highlight this modul as the current modul';

// Moodle 3.0 Enhancement.
$string['editsection'] = 'Edit modul';
$string['deletesection'] = 'Delete modul';

// MDL-51802.
$string['editsectionname'] = 'Edit modul name';
$string['newsectionname'] = 'New name for modul {$a}';

// Moodle 2.4 Course format refactoring - MDL-35218.
$string['numbersections'] = 'Number of moduls';

// Setting general.
$string['default'] = 'Default - {$a}';

// Section image.
$string['sectionimage'] = 'Modul image';
$string['sectionimage_help'] = 'The modul image.';
$string['sectionimagealttext'] = 'Image alt text';
$string['sectionimagealttext_help'] = 'This text will be set as the image alt attribute.';

// Section break.
$string['sectionbreak'] = 'Section break';
$string['sectionbreak_help'] = 'Break in the mintcampus at this section.';
$string['sectionbreakheading'] = 'Section break heading';
$string['sectionbreakheading_help'] = 'Show this heading at the point this section breaks in the mintcampus.  HTML can be used.';

// Image container width.
$string['imagecontainerwidth'] = 'Set the image container width';
$string['imagecontainerwidth_help'] = 'Set the image container width to one of: 128, 192, 210, 256, 320, 384, 448, 512, 576, 640, 704 or 768';
$string['defaultimagecontainerwidth'] = 'Default width of the image container';
$string['defaultimagecontainerwidth_desc'] = 'The default width of the image container.';

// Image container ratio.
$string['imagecontainerratio'] = 'Set the image container ratio relative to the width';
$string['imagecontainerratio_help'] = 'Set the image container ratio to one of: 3-2, 3-1, 3-3, 2-3, 1-3, 4-3 or 3-4.';
$string['defaultimagecontainerratio'] = 'Default ratio of the image container relative to the width';
$string['defaultimagecontainerratio_desc'] = 'The default ratio of the image container relative to the width.';

// Image resize method.
$string['scale'] = 'Scale';
$string['crop'] = 'Crop';
$string['imageresizemethod'] = 'Set the image resize method';
$string['imageresizemethod_help'] = "Set the image resize method to: 'Scale' or 'Crop' when resizing the image to fit the container.";
$string['defaultimageresizemethod'] = 'Default image resize method';
$string['defaultimageresizemethod_desc'] = 'The default method of resizing the image to fit the container.';

// Displayed image type.
$string['original'] = 'Original';
$string['webp'] = 'WebP';
$string['defaultdisplayedimagefiletype'] = 'Displayed image type';
$string['defaultdisplayedimagefiletype_desc'] = 'Set the displayed image type.';

// Single page summary image.
$string['off'] = 'Off';
$string['centre'] = 'Centre';
$string['left'] = 'Left';
$string['right'] = 'Right';
$string['singlepagesummaryimage'] = 'Show the mintcampus image in the section summary';
$string['singlepagesummaryimage_help'] = "Show the mintcampus image for that section in the section summary when there is a summary in the section.";
$string['defaultsinglepagesummaryimage'] = 'Show the mintcampus image in the section summary';
$string['defaultsinglepagesummaryimage_desc'] = "Show the mintcampus image for that section in the section summary when there is a summary in the section.";

// Course contents input
$string['modulecontent_1'] = 'Course content 1';
$string['modulecontent_2'] = 'Course content 2';
$string['modulecontent_3'] = 'Course content 3';
$string['modulecontents_help'] = 'Here you should input one of the course contents. 
A content could be a course topic, module, or any other relevant information regarding the course. 
The list of contents is later displayed in the module tile.';

// Activity header
$string['coursecompletionbar'] = 'Course completion';
$string['sectioncompletionbar'] = 'Section completion';
$string['captionnext'] = 'Next';
$string['captionback'] = 'Back';

// Modal.
$string['popup'] = 'Use a popup';
$string['popup_help'] = "Display the section in a popup instead of navigating to a single section page.";
$string['defaultpopup'] = 'Use a popup';
$string['defaultpopup_desc'] = "Default display the section in a popup instead of navigating to a single section page.";

// Completion.
$string['showcompletion'] = 'Show completion';
$string['showcompletion_help'] = "Show the completion of the section on the mintcampus.";
$string['defaultshowcompletion'] = 'Show completion';
$string['defaultshowcompletion_desc'] = "Default show the completion of the section on the mintcampus.";


// Other.
$string['information'] = 'Information';
$string['informationsettings'] = 'Information settings';
$string['informationsettingsdesc'] = 'mintcampus format information';
$string['informationchanges'] = 'Changes';
$string['sectionchangecoursesettings'] = 'Change the number of sections in the course settings';
$string['settings'] = 'Settings';
$string['settingssettings'] = 'Settings settings';
$string['settingssettingsdesc'] = 'mintcampus format settings';
$string['startcourse'] = "Start Course";
$string['continuecourse'] = "Continue Course";
$string['coursecompleted'] = "Course Completed";
$string['love'] = 'love';
$string['versioninfo'] = 'Release {$a->release}, version {$a->version} on Moodle {$a->moodle}.  Made with {$a->love} in Great Britain.';
$string['versionalpha'] = 'Alpha version - Almost certainly contains bugs.  This is a development version for developers \'only\'!  Don\'t even think of installing on a production server!';
$string['versionbeta'] = 'Beta version - Likely to contain bugs.  Ready for testing by administrators on a test server only.';
$string['versionrc'] = 'Release candidate version - May contain bugs.  Check completely on a test server before considering on a production server.';
$string['versionstable'] = 'Stable version - Could contain bugs.  Check on a test server before installing on your production server.';
$string['videocourse'] = 'Video file';
$string['mintcampuscoursevideo_filemanager_help'] = 'Video file for courseview';
$string['mintcampuscoursevideo_filemanager'] = 'Video file';
$string['mintcampuscourseimage_filemanager_help'] = 'Image file for courseview';
$string['mintcampuscourseimage_filemanager'] = 'Image file';
$string['courserating'] = 'Course rating';
$string['courseratingquestion'] = 'How do you like this course?';
$string['courseratingcomment'] = 'Leave a comment:';
$string['placeholder'] = 'Write a comment here';
$string['ratingsavesuccessheader'] = 'Course rating';
$string['ratecourse'] = 'Course rating';
$string['ratingsavesuccess'] = 'The course rating was successfully saved.';
$string['close'] = 'Close';
$string['noactivity'] = 'Please add activity to first section';
$string['noforumpost'] = 'No post on the forum yet.';
$string['nocoursesummary'] = 'Please add course summary.';
$string['nosectionactivity'] = 'Please add activity';
$string['hidefrommenu'] = 'Hide from menu';

// Exception messages.
$string['cannotconvertuploadedimagetodisplayedimage'] = 'Cannot convert uploaded image to displayed image - {$a}.  Please report error details and the information contained in the php.log file to developer.';
$string['cannotgetmanagesectionimagelock'] = 'Cannot get manage section image lock.  This can happen if two people are editing the settinsg of the same section on the same course at the same time.';
$string['formatnotsupported'] = 'Format is not supported at this server, please fix the system configuration to have the GD PHP extension installed - {$a}.';
$string['functionfailed'] = 'Function failed on image - {$a}.';
$string['mimetypenotsupported'] = 'Mime type is not supported as an image format in the mintcampus format - {$a}.';
$string['originalheightempty'] = 'Original height is empty - {$a}.';
$string['originalwidthempty'] = 'Original width is empty - {$a}.';
$string['noimageinformation'] = 'Image information is empty - {$a}.';
$string['reporterror'] = 'Please report error details and the information contained in the php.log file to developer';

// Privacy.
$string['privacy:nop'] = 'The mintcampus format stores lots of settings that pertain to its configuration.  None of the settings are related to a specific user.  It is your responsibilty to ensure that no user data is entered in any of the free text fields.  Setting a setting will result in that action being logged within the core Moodle logging system against the user whom changed it, this is outside of the formats control, please see the core logging system for privacy compliance for this.  When uploading images, you should avoid uploading images with embedded location data (EXIF GPS) included or other such personal data.  It would be possible to extract any location / personal data from the images.  Please examine the code carefully to be sure that it complies with your interpretation of your privacy laws.  I am not a lawyer and my analysis is based on my interpretation.  If you have any doubt then remove the format forthwith.';