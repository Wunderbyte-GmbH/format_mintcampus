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
 * Contains the default activity item from a section.
  *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @author     Based on code originally written 2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace format_mintcampus\output\courseformat\content\section;

use cm_info;
use core\output\named_templatable;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use renderable;
use renderer_base;
use section_info;
use stdClass;

/**
 * Base class to render a section activity in the activities list.
  *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @author     Based on code originally written 2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class cmitem extends \core_courseformat\output\local\content\section\cmitem {

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param cm_info $mod the course module ionfo
     * @param array $displayoptions optional extra display options
     */
    public function __construct(course_format $format, section_info $section, cm_info $mod, array $displayoptions = []) {
        parent::__construct($format,  $section,$mod,  $displayoptions);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {
        global $DB;
        $format = $this->format;
        $course = $format->get_course();
        $mod = $this->mod;

        $data = new stdClass();
        $data->cms = [];
        $accordiontype = '';
        $accordionheading='';
        if($mod->modname=='accordion'){
            $accordion = $DB->get_record('accordion', array('id' => $mod->instance));
            $accordiontype = $accordion->type;
            $accordionheading = $accordion->name;
        }

        $completionenabled = $course->enablecompletion == COMPLETION_ENABLED;
        $showactivityconditions = $completionenabled && $course->showcompletionconditions == COMPLETION_SHOW_CONDITIONS;
        $showactivitydates = !empty($course->showactivitydates);

        // This will apply styles to the course homepage when the activity information output component is displayed.
        $hasinfo = $showactivityconditions || $showactivitydates;

        $item = new $this->cmclass($format, $this->section, $mod, $this->displayoptions);
        return (object)[
            'id' => $mod->id,
            'anchor' => "module-{$mod->id}",
            'module' => $mod->modname,
            'extraclasses' => $mod->extraclasses,
            'cmformat' => $item->export_for_template($output),
            'hasinfo' => $hasinfo,
            'accordiontype'=> $accordiontype,
            'accordionheading'=> $accordionheading,
        ];
    }
}
