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
 * Contains the default activity name inplace editable.
  *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @author     Based on code originally written 2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace format_mintcampus\output\courseformat\content\cm;

use cm_info;
use context_module;
use core\output\inplace_editable;
use core\output\named_templatable;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use external_api;
use lang_string;
use renderable;
use section_info;
use stdClass;

/**
 * Base class to render a course module inplace editable header.
  *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @author     Based on code originally written 2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class cmname extends \core_courseformat\output\local\content\cm\cmname {
    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param cm_info $mod the course module ionfo
     * @param bool $editable if it is editable
     * @param array $displayoptions optional extra display options
     */
    public function __construct(course_format $format, section_info $section, cm_info $mod, ?bool $editable = null, array $displayoptions = []) {
        parent::__construct($format,$section,$mod,$editable,$displayoptions);
        $this->section=$section;
        $this->editable=$editable;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): array {
        global $CFG,$DB;
        $mod = $this->mod;
        $displayoptions = $this->displayoptions;

        if (!$this->has_name()) {
            // Nothing to be displayed to the user.
            return [];
        }

        if (file_exists($CFG->dirroot."/course/format/mintcampus/pix/modicons/".$mod->modname.".svg")) {
            $icon = $output->image_url("modicons/".$mod->modname, 'format_mintcampus');
        }else{
            if($mod->modname=='accordion'){
                $accordiontype= $DB->get_field('accordion','type' ,array('id'=>$mod->instance));
                $mod->set_icon_url($output->image_url('monologo'.$accordiontype, 'mod_accordion'));
                $icon = $mod->get_icon_url();
            }else{
                $icon = $mod->get_icon_url();
            }
        }

        $data = [
            'url' => $mod->url,
            'icon' => $icon,
            'modname' => $mod->modname,
            'textclasses' => $displayoptions['textclasses'] ?? '',
            'purpose' => plugin_supports('mod', $mod->modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER),
            'activityname' => $this->get_title_data($output),
        ];
        return $data;
    }
}
