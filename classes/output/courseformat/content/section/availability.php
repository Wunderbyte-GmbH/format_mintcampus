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
 * Contains the default section availability output class.
 *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @author     Based on code originally written 2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace format_mintcampus\output\courseformat\content\section;

use context_course;
use core_availability\info;
use core_availability\info_section;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use section_info;
use stdClass;

/**
 * Base class to render section availability.
 *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @author     Based on code originally written 2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class availability extends \core_courseformat\output\local\content\section\availability {
    use courseformat_named_templatable;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     */
    public function __construct(course_format $format, section_info $section) {
        parent::__construct($format, $section);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return stdClass|null data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): ?stdClass {
        $this->build_export_data($output);
        return $this->data;
    }

    /**
     * Returns if the output has availability info to display.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return bool if the element has availability data to display
     */
    public function has_availability(\renderer_base $output): bool {
        $this->build_export_data($output);
        $attributename = $this->hasavailabilityname;
        return $this->data->$attributename;
    }
}
