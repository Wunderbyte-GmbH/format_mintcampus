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
 * @category   event
 * @version    See the value of '$plugin->version' in version.php.

 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// List of observers.
$observers = [

    [
        'eventname' => '\core\event\course_content_deleted',
        'callback' => 'format_mintcampus_observer::course_content_deleted',
    ],
    [
        'eventname' => '\core\event\course_restored',
        'callback' => 'format_mintcampus_observer::course_restored',
    ],
];
