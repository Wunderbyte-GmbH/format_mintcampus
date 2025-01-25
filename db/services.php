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
 * Chat external functions and service definitions.
 *
 * @package     format_mintcampus
 * @category    external
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
$functions = array(
    'format_mintcampus_get_activity_navigation' => array(
        'classname' => 'format_mintcampus_external',
        'methodname' => 'get_activity_navigation',
        'classpath' => 'course/format/mintcampus/external.php',
        'description' => 'Fetches a data for activity navigation',
        'type' => 'write',
        'ajax' => true,
        'capabilities'  => ''
    ),
    'format_mintcampus_save_rating' => array(
        'classname' => 'format_mintcampus_external',
        'methodname' => 'add_rating',
        'classpath' => 'course/format/mintcampus/external.php',
        'description'   => 'Submit the rating data via ajax',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'format_mintcampus_save_comment' => array(
        'classname' => 'format_mintcampus_external',
        'methodname' => 'add_comment',
        'classpath' => 'course/format/mintcampus/external.php',
        'description'   => 'Submit the rating comment via ajax',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'format_mintcampus_get_rating' => array(
        'classname' => 'format_mintcampus_external',
        'methodname' => 'get_rating',
        'classpath' => 'course/format/mintcampus/external.php',
        'description'   => 'Get template for rating',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'format_mintcampus_get_activity_setting' => array(
        'classname' => 'format_mintcampus_external',
        'methodname' => 'get_activity_setting',
        'classpath' => 'course/format/mintcampus/external.php',
        'description'   => 'Get toggle settings',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'format_mintcampus_set_activity_setting' => array(
        'classname' => 'format_mintcampus_external',
        'methodname' => 'set_activity_setting',
        'classpath' => 'course/format/mintcampus/external.php',
        'description'   => 'Set toggle settings',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'format_mintcampus_delete_rating' => [
        'classname'   => 'format_mintcampus_external',
        'methodname'  => 'delete_rating',
        'classpath'   => 'course/format/mintcampus/externallib.php',
        'description' => 'Delete a rating from the database.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'moodle/course:manageactivities',
    ]
    );

