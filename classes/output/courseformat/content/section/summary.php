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
 * Contains the default section summary (used for multipage format).
 *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in version.php.

 * @copyright  2020 Ferran Recio <ferran@moodle.com>
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_mintcampus\output\courseformat\content\section;

use core_courseformat\output\local\content\section\summary as summary_base;
use core_courseformat\base as course_format;
use section_info;
use stdClass;

/**
 * Base class to render a course section summary.
 *
 * @package   core_courseformat
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class summary extends summary_base {

    /** @var section_info the course section class - core is 'private' */
    private $thesection;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     */
    public function __construct(course_format $format, section_info $section) {
        parent::__construct($format, $section);
        $this->thesection = $section;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {

        $section = $this->thesection;

        $data = new stdClass();
        return $data;
    }

    /**
     * Generate html for a section summary image
     * @param string $summary The summary text if any.
     * @param object $output The output renderer.
     *
     * @return string HTML to output.
     */
    protected function singlepagesummaryimage($summary, $output): string {
        global $DB;
        $o = '';

        if (!empty($summary)) {
            $o = $summary;
        }

        return $o;
    }
}
