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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use format_mintcampus\admin_setting_information;
use format_mintcampus\admin_setting_markdown;

require_once($CFG->dirroot . '/course/format/mintcampus/lib.php'); // For format_mintcampus static constants.

$settings = null;
$ADMIN->add('formatsettings', new admin_category('format_mintcampus', get_string('pluginname', 'format_mintcampus')));

// Information.
$page = new admin_settingpage('format_mintcampus_information',
    get_string('information', 'format_mintcampus'));

if ($ADMIN->fulltree) {
    $page->add(new admin_setting_heading('format_mintcampus_information', '',
        format_text(get_string('informationsettingsdesc', 'format_mintcampus'), FORMAT_MARKDOWN)));

    // Information.
    $page->add(new admin_setting_information('format_mintcampus/formatinformation', '', '', 401));

    // Support.md.
    $page->add(new admin_setting_markdown('format_mintcampus/formatsupport', '', '', 'Support.md'));

    // Changes.md.
    $page->add(new admin_setting_markdown('format_mintcampus/formatchanges',
        get_string('informationchanges', 'format_mintcampus'), '', 'Changes.md'));
}
$ADMIN->add('format_mintcampus', $page);
