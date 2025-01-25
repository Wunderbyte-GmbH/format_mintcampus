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
 * Contains the default activity list from a section.
  *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.

 * @author     Based on code originally written 2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace format_mintcampus\output\courseformat\content;

use cm_info;
use core\activity_dates;
use core\output\named_templatable;
use core_availability\info_module;
use core_completion\cm_completion_details;
use core_course\output\activity_information;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use renderable;
use renderer_base;
use section_info;
use stdClass;

/**
 * Base class to render a course module inside a course format.
  *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @author     Based on code originally written 2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class cm extends \core_courseformat\output\local\content\cm {

    private $section;
    /**
     * @param course_format $format
     * @param \section_info $section
     * @param \cm_info $mod
     * @param array $displayoptions
     */
    public function __construct(course_format $format, \section_info $section, \cm_info $mod, array $displayoptions = []) {
        parent::__construct($format, $section, $mod, $displayoptions);
        $this->section =$section;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        $mod = $this->mod;
        $displayoptions = $this->displayoptions;

        $data = (object)[
            'grouping' => $mod->get_grouping_label($displayoptions['textclasses']),
            'modname' => get_string('pluginname', 'mod_' . $mod->modname),
            'url' => $mod->url,
            'activityname' => $mod->get_formatted_name(),
            'textclasses' => $displayoptions['textclasses'],
            'classlist' => [],
        ];

        // Add partial data segments.
        $haspartials = [];
        $haspartials['cmname'] = $this->add_cm_name_data($data, $output);
        $haspartials['availability'] = $this->add_availability_data($data, $output);
        $haspartials['duration'] = $this->add_duration_content_data($data, $output);//additional
        $haspartials['alternative'] = $this->add_alternative_content_data($data, $output);
        $haspartials['completion'] = $this->add_completion_data($data, $output);
        $haspartials['editor'] = $this->add_editor_data($data, $output);
        $this->add_format_data($data, $haspartials, $output);

        // Calculated fields.
        if (!empty($data->url)) {
            $data->hasurl = true;
        }
        return $data;
    }

    /**
     * Add the alternative content to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has alternative content
     */
    protected function add_alternative_content_data(stdClass &$data, renderer_base $output): bool {
        $altcontent = $this->mod->get_formatted_content(
                ['overflowdiv' => true, 'noclean' => true]
        );

        if(!empty($altcontent)){
            $altcontent = preg_replace('~<duration(.*?)</duration>~Usi', "", $altcontent);
        }
           $data->notlabel = $data->modname!='Label';
           $data->altcontent = (empty($altcontent)) ? false : $altcontent;
           $data->afterlink = $this->mod->afterlink;

        return !empty($data->altcontent);
    }

    /**
     * Add the alternative content to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has alternative content
     */
    protected function add_duration_content_data(stdClass &$data,  $output): bool {
        global $DB;
        $moduleinstance = $DB->get_record($this->mod->modname, array('id' => $this->mod->instance), '*', MUST_EXIST);
        $data->durationtext=false;
       if($moduleinstance->intro){
           preg_match('/<duration>(.*?)<\/duration>/s', $moduleinstance->intro, $match);

           $data->notlabel = $data->modname!='Label';
           if(!empty($match)){
               $data->durationtext= format_string($match[1]);
           }

       }
        return !empty($data->durationtext);
    }

    /**
     * Add course module name attributes to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has name data
     */
    protected function add_cm_name_data(stdClass &$data, renderer_base $output): bool {
        // Mod inplace name editable.
        $cmname = new $this->cmnameclass(
            $this->format,
            $this->section,
            $this->mod,
            null,
            $this->displayoptions
        );
        $data->cmname = $cmname->export_for_template($output);
        $data->hasname = $cmname->has_name();
        return $data->hasname;
    }

    /**
     * Add activity completion information to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool the module has completion information
     */
    protected function add_completion_data(stdClass &$data, renderer_base $output): bool {
        global $USER;
        $course = $this->mod->get_course();
        // Fetch completion details.
        $showcompletionconditions = $course->showcompletionconditions == COMPLETION_SHOW_CONDITIONS;
        $completiondetails = cm_completion_details::get_instance($this->mod, $USER->id, $showcompletionconditions);

        // Fetch activity dates.
        $activitydates = [];
        if ($course->showactivitydates) {
            $activitydates = activity_dates::get_dates_for_module($this->mod, $USER->id);
        }

        $activityinfodata = (object) ['hasdates' => false, 'hascompletion' => false];
        // There are activity dates to be shown; or
        // Completion info needs to be displayed
        // * The activity tracks completion; AND
        // * The showcompletionconditions setting is enabled OR an activity that tracks manual
        // completion needs the manual completion button to be displayed on the course homepage.
        $showcompletioninfo = $completiondetails->has_completion() && ($showcompletionconditions ||
                (!$completiondetails->is_automatic() && $completiondetails->show_manual_completion()));
        if ($showcompletioninfo || !empty($activitydates)) {
            $activityinfo = new activity_information($this->mod, $completiondetails, $activitydates);
            $activityinfodata = $activityinfo->export_for_template($output);
        }

        $data->activityinfo = $activityinfodata;
        $data->cmid = isset($activityinfodata->cmid) ?:false;
        return $activityinfodata->hascompletion;
    }
}
