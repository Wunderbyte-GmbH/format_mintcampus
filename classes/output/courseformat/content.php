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
 * @package   format_mintcampus
 * @copyright 2020 Ferran Recio <ferran@moodle.com>


 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_mintcampus\output\courseformat;

use core_courseformat\output\local\content as content_base;
use core_courseformat\output\local\content\section\summary;
use mod_forum\local\vaults\forum;
use stdClass;

require_once($CFG->dirroot . '/course/format/mintcampus/locallib.php');

/**
 * Base class to render a course content.
 *
 * @package   format_mintcampus
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {

    private $sectioncompletionpercentage = array();
    private $sectioncompletionmarkup = array();
    private $sectioncompletioncalculated = array();
    private $courseformat;
    const MODULE_CONTENTS_NUM = 3;
    const MODULE_CONTENT_FIELD_NAME_BASE = 'modulecontent_';

    /**
     * @var bool mintcampus format does not add section after each topic.
     *
     * The responsible for the buttons is core_courseformat\output\local\content\section.
     */
    protected $hasaddsection = true;

    public function get_template_name(\renderer_base $renderer): string {
        return 'format_mintcampus/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a Mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $DB, $PAGE;
        $format = $this->format;
        $editing = $PAGE->user_is_editing();

        $data = (object)[
            'title' => $format->page_title(),
            'format' => $format->get_format(),
            'sectionreturn' => 0,
            'editing' => $editing,
        ];

        $singlesection = $this->format->get_section_number();
        $sections = $this->export_sections($output);
        $initialsection = '';
        $course = $format->get_course();
        $this->courseformat = course_get_format($course);
        $currentsectionid = 0;

        if ($editing) {
            $data->coursesettings = new \moodle_url('/course/edit.php', array('id' => $course->id));
            $data->courseid = $course->id;
        }

        if (!empty($sections)) {
            // Most formats uses section 0 as a separate section so we remove from the list.
            $initialsection = array_shift($sections);
            $coursesettings = $format->get_settings();

            if (!$singlesection && !$editing) {
                $coursecontext = \context_course::instance($course->id);
                $data->initialsection = $this->export_initial_section($output, $coursesettings,$coursecontext);
                $modinfo = get_fast_modinfo($course);
                $sectioninfo = $modinfo->get_section_info_all();
                $cmlist = new content\section\cmlist($format,$sectioninfo[0]);
                $data->initialcmlist= $cmlist->export_for_template($output);
            }elseif(!$singlesection){
                $data->initialsection = $initialsection;
            }
            if (($editing) || ($singlesection)) { // This triggers the display of the standard list of section(s).
                $data->sections = $sections;
            }
            if (!empty($course->marker)) {
                foreach ($sections as $section) {
                    if ($section->num == $course->marker) {
                        $currentsectionid = $section->id;
                        break;
                    }
                }
            }
        }

        // The single section format has extra navigation.
        if ($singlesection) {
            $sectionnavigation = new $this->sectionnavigationclass($format, $singlesection);
            $data->sectionnavigation = $sectionnavigation->export_for_template($output);

            $sectionselector = new $this->sectionselectorclass($format, $sectionnavigation);
            $data->sectionselector = $sectionselector->export_for_template($output);
            $data->hasnavigation = true;
            $data->singlesection = array_shift($data->sections);
            $data->sectionreturn = $singlesection;
            $data->maincoursepage = new \moodle_url('/course/view.php', array('id' => $course->id));
        } else {
            $coursesettings = $format->get_settings();
            $toolbox = \format_mintcampus\toolbox::get_instance();
            $coursesectionimages = $DB->get_records('format_mintcampus_image', array('courseid' => $course->id));
            if (!empty($coursesectionimages)) {
                $fs = get_file_storage();
                $coursecontext = \context_course::instance($course->id);
                foreach ($coursesectionimages as $coursesectionimage) {
                    $replacement = $toolbox->check_displayed_image($coursesectionimage, $course->id, $coursecontext->id,
                        $coursesectionimage->sectionid, $format, $fs);
                    if (!empty($replacement)) {
                        $coursesectionimages[$coursesectionimage->id] = $replacement;
                    }
                }
            }

            // Popup.
            if (!$editing) {
                $data->popup = false;
                if ((!empty($coursesettings['popup'])) && ($coursesettings['popup'] == 2)) {
                    $data->popup = true;
                    $data->popupsections = array();
                    $potentialpopupsections = array();
                    foreach ($sections as $section) {
                        $potentialpopupsections[$section->id] = $section;
                    }
                }
            }

            // Suitable array.
            $sectionimages = array();
            if (!empty($coursesectionimages)) {
                foreach ($coursesectionimages as $coursesectionimage) {
                    $sectionimages[$coursesectionimage->sectionid] = $coursesectionimage;
                }
            }
            // Now iterate over the sections.
            $data->mintcampussections = array();
            $sectionsformintcampus = $this->get_mintcampus_sections($output, $coursesettings);
            $iswebp = false;

            $completionshown = false;
            $headerimages = false;
            if ($editing) {
                $datasectionmap = array();
                foreach ($data->sections as $datasectionkey => $datasection) {
                    $datasectionmap[$datasection->id] = $datasectionkey;
                }
            }
            foreach ($sectionsformintcampus as $section) {
                // Do we have an image?
                if ((array_key_exists($section->id, $sectionimages)) && ($sectionimages[$section->id]->displayedimagestate >= 1)) {
                    $sectionimages[$section->id]->imageuri = $toolbox->get_displayed_image_uri(
                        $sectionimages[$section->id], $coursecontext->id, $section->id, $iswebp);
                } else {
                    // No.
                    $sectionimages[$section->id] = new stdClass;
                    $sectionimages[$section->id]->generatedimageuri = $output->get_generated_image_for_id($section->id);
                }
                // Number.
                $sectionimages[$section->id]->number = $section->num;
                $sectionimages[$section->id]->scgraphic = $section->scgraphic;
//                $sectionimages[$section->id]->summary = $section->summary;

                // Alt text.
                $sectionformatoptions = $format->get_format_options($section);
                $sectionimages[$section->id]->imagealttext = $sectionformatoptions['sectionimagealttext'];

                $summary = $this->make_list_of_module_contents($sectionformatoptions);
                $sectionimages[$section->id]->summary = $this->format_summary($section->num, $summary, 350);

                // Current section?
                if ((!empty($currentsectionid)) && ($currentsectionid == $section->id)) {
                    $sectionimages[$section->id]->currentsection = true;
                }

                if ($editing) {
                    if (!empty($data->sections[$section->num])) {
                        // Add the image to the section content.
                        $data->sections[$datasectionmap[$section->id]]->mintcampusimage = $sectionimages[$section->id];
                        $headerimages = true;
                    }
                } else {
                    // Section link.
                    if($section->activitylink){
                        $sectionimages[$section->id]->sectionurl = $section->activitylink;
                    }else{
                        $sectionimages[$section->id]->sectionurl = false;
                    }

                    // Section name.
                    $sectionimages[$section->id]->sectionname = $section->name;

                    // Section break.
                    if ($sectionformatoptions['sectionbreak'] == 2) { // Yes.
                        $sectionimages[$section->id]->sectionbreak = true;
                        if (!empty ($sectionformatoptions['sectionbreakheading'])) {
                            // Note:  As a PARAM_TEXT, then does need to be passed through 'format_string' for multi-lang or not?
                            $sectionimages[$section->id]->sectionbreakheading = format_text(
                                $sectionformatoptions['sectionbreakheading'],
                                FORMAT_HTML
                            );
                        }
                    }

                    // Completion?
                    if (!empty($section->sectioncompletionmarkup)) {
                        $sectionimages[$section->id]->sectioncompletionmarkup = $section->sectioncompletionmarkup;
                        $completionshown = true;
                    }

                    // For the template.
                    $data->mintcampussections[] = $sectionimages[$section->id];
                    if ($data->popup) {
                        $data->popupsections[] = $potentialpopupsections[$section->id];
                    }
                }
            }

            $data->hasmintcampussections = !empty($data->mintcampussections);
            if ($data->hasmintcampussections) {
                $data->coursestyles = $toolbox->get_displayed_image_container_properties($coursesettings);
            }

            if ($headerimages) {
                $data->hasheaderimages = true;
                $coursesettings['imagecontainerwidth'] = 144;
                $data->coursestyles = $toolbox->get_displayed_image_container_properties($coursesettings);
            }
        }

        if ($this->hasaddsection) {
            $addsection = new $this->addsectionclass($format);
            $data->numsections = $addsection->export_for_template($output);
        }
        return $data;
    }

    /**
     * Export sections array data.
     *
     * @param renderer_base $output typically, the renderer that's calling this method.
     * @param array $settings The settings for the format.
     *
     * @return array data context for a mustache template
     */
    protected function get_mintcampus_sections(\renderer_base $output, $settings): array {

        $format = $this->format;
        $course = $format->get_course();
        $modinfo = $this->format->get_modinfo();

        // Generate section list.
        $sections = [];
        $numsections = $format->get_last_section_number();
        $sectioninfos = $modinfo->get_section_info_all();
        // Get rid of section 0.
        if (!empty($sectioninfos)) {
            array_shift($sectioninfos);
        }
        foreach ($sectioninfos as $thissection) {
            // The course/view.php check the section existence but the output can be called from other parts so we need to check it.
            if (!$thissection) {
                throw new \moodle_exception('unknowncoursesection', 'error', '',
                    get_string('unknowncoursesection', 'error',
                        course_get_url($course).' - '.format_string($course->fullname))
                    );
            }

            if ($thissection->section > $numsections) {
                continue;
            }

            if (!$format->is_section_visible($thissection)) {
                continue;
            }

            $section = new stdClass;
            $section->id = $thissection->id;
            $section->num = $thissection->section;
            $section->name = $output->section_title_without_link($thissection, $course);
            $section->scgraphic = $this->section_completion_graphic($thissection, $course, false, $output);
//            $section->summary = $this->make_section_summary($thissection, 200, '<br><ul><li>');
            $section->activitylink = $this->get_section_activity_link($thissection->section,$course);
            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * Calculate and generate the markup for completion of the activities in a section.
     *
     * @param stdClass $section The course_section.
     * @param stdClass $course the course.
     * @param stdClass $modinfo the course module information.
     * @param renderer_base $output typically, the renderer that's calling this method.
     */
    protected function calculate_section_activity_completion($section, $course, $modinfo, \renderer_base $output) {
        if (empty($this->sectioncompletioncalculated[$section->section])) {
            $this->sectioncompletionmarkup[$section->section] = '';
            if (empty($modinfo->sections[$section->section])) {
                $this->sectioncompletioncalculated[$section->section] = true;
                return;
            }

            // Generate array with count of activities in this section.
            $total = 0;
            $complete = 0;
            $cancomplete = isloggedin() && !isguestuser();
            $asectionisvisible = false;
            if ($cancomplete) {
                $completioninfo = new \completion_info($course);
                foreach ($modinfo->sections[$section->section] as $cmid) {
                    $thismod = $modinfo->cms[$cmid];

                    if ($thismod->visible) {
                        $asectionisvisible = true;
                        if ($completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                            $total++;
                            $completiondata = $completioninfo->get_data($thismod, true);
                            if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                                $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                                $complete++;
                            }
                        }
                    }
                }
            }

            if ((!$asectionisvisible) || (!$cancomplete)) {
                // No sections or no completion.
                $this->sectioncompletioncalculated[$section->section] = true;
                return;
            }

            // Output section completion data.
            if ($total > 0) {
                $percentage = round(($complete / $total) * 100);
                $this->sectioncompletionpercentage[$section->section] = $percentage;

                $data = new \stdClass();
                $data->percentagevalue = $this->sectioncompletionpercentage[$section->section];
                if ($data->percentagevalue < 11) {
                    $data->percentagecolour = 'low';
                } else if ($data->percentagevalue < 90) {
                    $data->percentagecolour = 'middle';
                } else {
                    $data->percentagecolour = 'high';
                }
                if ($data->percentagevalue < 1) {
                    $data->percentagequarter = 0;
                } else if ($data->percentagevalue < 26) {
                    $data->percentagequarter = 1;
                } else if ($data->percentagevalue < 51) {
                    $data->percentagequarter = 2;
                } else if ($data->percentagevalue < 76) {
                    $data->percentagequarter = 3;
                } else {
                    $data->percentagequarter = 4;
                }
                $this->sectioncompletionmarkup[$section->section] = $output->render_from_template('format_mintcampus/mintcampus_completion', $data);
            }

            $this->sectioncompletioncalculated[$section->section] = true;
        }
    }

    /**
     * Generate the section completion graphic if any.
     *
     * @param stdClass $section The course_section entry from DB.
     * @param stdClass $course the course record from DB.
     * @return string the markup or empty if nothing to show.
     */
    public function section_completion_graphic($section, $course, $activitiesstat,\renderer_base $output) {
        $markup = '';
        if (($course->enablecompletion)) {
            if(list($complete,$total)= $this->section_activity_progress($section, $course)){
                $percentage = round(($complete / $total) * 100);
                if($activitiesstat){
                    $progressbar = ['progressbar'=>['percents'=>$percentage,'activities'=>"{$complete}/{$total}",'primarycolor'=>"#8139a3",'secondarycolor'=>'#d0b5dd','fontcolor'=>'#ffffff']];
                    $markup = $output->render_from_template('format_mintcampus/progressbar', $progressbar);
                }else{
                    $progressbar = ['progressbar'=>['percents'=>$percentage,'activities'=>null,'primarycolor'=>"#8139a3",'secondarycolor'=>'#d0b5dd','fontcolor'=>'#ffffff']];
                    $markup = $output->render_from_template('format_mintcampus/progressbar', $progressbar);
                }
            }
        }
        return $markup;
    }

    private function make_section_summary($thissection, $charlength, $allowedtags = '<br><p>') {
        $summary = new summary($this->courseformat, $thissection);

        $summary = $summary->format_summary_text();
        $summary = strip_tags($summary, $allowedtags);

        if (!empty($summary)) {
            return $this->format_summary($thissection->section, $summary, $charlength);
        }

        return null;
    }

    private function make_list_of_module_contents($sectionformatoptions): string
    {
        $modulecontents = array();
        for ($i = 1; $i <= self::MODULE_CONTENTS_NUM; $i++) {
            $fieldname = content::MODULE_CONTENT_FIELD_NAME_BASE . $i;
            if (!empty($sectionformatoptions[$fieldname])) {
                $modulecontents[] = $sectionformatoptions[$fieldname];
            }
        }

        return \html_writer::alist(
            $modulecontents,
            array('class' => 'module-contents')
        );
    }

    private function format_summary($section, $summary, $charlength) {
        $summarylen = \core_text::strlen($summary);
        if ($summarylen !== false) {
            if ($summarylen > $charlength) {
                $summary = \core_text::substr($summary, 0, $charlength);

                $pattern = "/<(\/?[a-zA-Z][a-zA-Z0-9]*)[^>]*>/";
                preg_match_all($pattern, $summary, $matches);

                $tags = $matches[1];
                $closingTags = array_filter($tags, function($tag) {
                    return strpos($tag, '/') !== false;
                });

                foreach ($closingTags as $closingTag) {
                    $pinedTagIndex = null;
                    $openingTag = trim($closingTag, '/');
                    foreach ($tags as $index => $tag) {
                        if ($openingTag === $tag) {
                            $pinedTagIndex = $index;
                        }
                        if ($closingTag === $tag) {
                            if ($pinedTagIndex !== null) {
                                unset($tags[$index]);
                                unset($tags[$pinedTagIndex]);
                                $pinedTagIndex = null;
                            }
                        }
                    }
                }

                $missingClosingTags = array_reverse($tags);
                $missingClosingTags = array_map(function($tag) {
                    return "</$tag>";
                }, $missingClosingTags);

                $summary .= '...' . implode('', $missingClosingTags);
            }
        }
        return \html_writer::tag('div', $summary, array('id' => 'flexaccordsectionsummary-'.$section ,'class' => 'sectionsummary'));
    }

    private function make_course_summary($course, $charlength) {

        $options = new stdClass();
        $options->noclean = false;
        $options->overflowdiv = false;

        $summary = format_text($course->summary, $course->summaryformat,$options);
        $summary = strip_tags($summary, '<br> <p> <strong> <ol> <li> <ul>');

        if (!empty($summary) && $summary != "") {
            $data = \html_writer::div($summary,'sectionsummary', array('id' => 'flexaccordcoursesummary-0'));
            return $data;
        }else{
            return \html_writer::tag('p',get_string('nocoursesummary','format_mintcampus'),['class'=>'alert alert-info']);
        }

        return null;
    }

    /**
     * Calculate the section progress.
     *
     * Adapted from core section_activity_summary() method.
     *
     * @param stdClass $section The course_section entry from DB.
     * @param stdClass $course the course record from DB.
     * @return bool/int false if none or the actual progress.
     */
    protected function section_activity_progress($section, $course) {
        $modinfo = get_fast_modinfo($course);
        if ($section&&empty($modinfo->sections[$section->section])) {
            return false;
        }

        // Generate array with count of activities in this section:
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new \completion_info($course);

        if($section){
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $thismod = $modinfo->cms[$cmid];

                // Labels counted for now, see: https://tracker.moodle.org/browse/MDL-65853.

                if ($thismod->uservisible) {
                    if (isset($sectionmods[$thismod->modname])) {
                        $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                        $sectionmods[$thismod->modname]['count']++;
                    } else {
                        $sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
                        $sectionmods[$thismod->modname]['count'] = 1;
                    }
                    if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                        $total++;
                        $completiondata = $completioninfo->get_data($thismod, true);
                        if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                            $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                            $complete++;
                        }
                    }
                }
            }
        }else{
            foreach ($modinfo->sections as $ssection){
                foreach ($ssection as $cmid) {
                    $thismod = $modinfo->cms[$cmid];

                    // Labels counted for now, see: https://tracker.moodle.org/browse/MDL-65853.

                    if ($thismod->uservisible) {
                        if (isset($sectionmods[$thismod->modname])) {
                            $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                            $sectionmods[$thismod->modname]['count']++;
                        } else {
                            $sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
                            $sectionmods[$thismod->modname]['count'] = 1;
                        }
                        if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                            $total++;
                            $completiondata = $completioninfo->get_data($thismod, true);
                            if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                                $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                                $complete++;
                            }
                        }
                    }
                }
            }
        }

        if (empty($sectionmods)) {
            // No sections
            return false;
        }

        if ($total == 0) {
            return false;
        }

        return [$complete, $total];
    }

    /**
     * Export sections array data.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    protected function export_initial_section(\renderer_base $output, $settings, $coursecontext) {
        global $PAGE, $DB;

        $format = $this->format;
        $course = $format->get_course();
        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        $mediamanager = \core_media_manager::instance($PAGE);
        $embedoptions = array(
            \core_media_manager::OPTION_TRUSTED => true,
            \core_media_manager::OPTION_BLOCK => true
        );

        $generatedimageuri = false;
        if($videomoodleurl = format_mintcampus_get_video($coursecontext)){
            $coursevideoimage = $mediamanager->embed_url($videomoodleurl, 'Course video', 0, 0, $embedoptions);
        } else if ($imagemoodleurl = format_mintcampus_get_image($coursecontext)){
            $coursevideoimage = resourcelib_embed_image($imagemoodleurl->out(), 'Course image');
        }else{
            $generatedimageuri = true;
            $coursevideoimage = $this->get_dummy_image_for_id($course->id);
        }
        $section=$sections[0];
        // Generate section list

//        $courserating = \html_writer::tag('i','',['id'=>'mintcampusrating','class'=>'fa fa-star-half-o fa-3','aria-hidden'=>'true']);
        $courserating = '';
        $starsnum = 5;
        $rating = $DB->get_record_sql(
            "SELECT
                    courseid,
                    COUNT(courseid) as reviewsnum,
                    AVG(rating) AS score
                FROM {format_mintcampus_ratings}
                WHERE courseid = :courseid
                GROUP BY courseid",
            array('courseid' => $course->id)
        );

        if ($rating) {
            $courserating = $rating->score;
            $reviews = $rating->reviewsnum;
                    // Localize the rating (replace the decimal point with a comma)
        $loccourserating = str_replace('.', ',', number_format($courserating, 1));

        // Initialize variables for star count
        $fullstars = floor($courserating);  // Number of full stars
        $halfstars = ($courserating - $fullstars >= 0.5) ? 1 : 0;  // Half star if decimal >= 0.5
        $emptystars = $starsnum - ($fullstars + $halfstars);  // Remaining stars will be empty

        // Start rendering the stars
        $starsoutput = '';
        for ($i = 0; $i < $fullstars; $i++) {
            $starsoutput .= \html_writer::tag('i', '', ['id' => 'mintcampusrating', 'class' => 'fa fa-star fa-3', 'aria-hidden' => 'true']);
        }

        if ($halfstars) {
            $starsoutput .= \html_writer::tag('i', '', ['id' => 'mintcampusrating', 'class' => 'fa fa-star-half-o fa-3', 'aria-hidden' => 'true']);
        }

        for ($i = 0; $i < $emptystars; $i++) {
            $starsoutput .= \html_writer::tag('i', '', ['id' => 'mintcampusrating', 'class' => 'fa fa-star-o fa-3', 'aria-hidden' => 'true']);
        }

        $courserating = '<div class="rating-container">
        <div class="rating-number">' . $loccourserating . '
        </div>
        <div>
        <div class="stars">
            ' . $starsoutput . '
        </div>
        <div class="review-text">
            Aus '. $reviews . ' Bewertungen
        </div>
    </div> </div>';
        } else {
            for ($i = 0; $i < 5; $i++) {
                $starsoutput .= \html_writer::tag('i', '', ['id' => 'mintcampusrating', 'class' => 'fa fa-star-o fa-3', 'aria-hidden' => 'true']);
            }


            $courserating = '<div class="rating-container">
                <div class="rating-number">
                </div>
                <div>
                <div class="stars">
                    ' . $starsoutput . '
                </div>
                <div class="review-text">
                    Aus '. 0 . ' Bewertungen
                </div>
                </div> </div>';
        }


        $data = (object)[
            'num' => $section->section ?? '0',
            'id' => $section->id,
            'sectionreturn' => $format->get_section_number(),
            'insertafter' => false,
            'summary' => $this->make_course_summary($course,250),
            'highlightedlabel' => $format->get_section_highlighted_name(),
            'sitehome' => $course->id == SITEID,
            'editing' => $PAGE->user_is_editing(),
            'sectionname'=>$format->get_section_name($section),
            'scgraphic'=> $this->section_completion_graphic(false, $course, false, $output),
            'coursevideoimage'=> $coursevideoimage,
            'forumpost' => $this->get_last_forum_post($course) ? $this->get_last_forum_post($course) : false,//no post if false
            'generatedimageuri'=>$generatedimageuri,
            'courserating' => $courserating,
            'courseid'=> $course->id
        ];

        $this->get_course_action_button($data,$course);

        return $data;
    }

    /**
     * Get last forum post from section 0
     *
     * @param $course
     * @return false|string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_last_forum_post($course){
        global $USER;

        if($forums = get_all_instances_in_course("forum", $course)){
            $vaultfactory = \mod_forum\local\container::get_vault_factory();
            $forumvault = $vaultfactory->get_forum_vault();

            foreach ($forums as $forum) {

                if ($forum->section==0) {

                    $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id);
                    $context = \context_module::instance($cm->id);
                    if (has_capability('mod/forum:viewdiscussion', $context)) {

                        $forum =  $forumvault->get_from_id($forum->id);

                        $discussionvault = $vaultfactory->get_discussion_vault();
                        if($discussion = $discussionvault->get_last_discussion_in_forum($forum)){
                            $posts = forum_get_all_discussion_posts($discussion->get_id(),'p.created DESC');
                            $post = array_shift($posts);

                            $postvault = $vaultfactory->get_post_vault();
                            $post = $postvault->get_from_id($post->id);

                            $rendererfactory = \mod_forum\local\container::get_renderer_factory();
                            $discussionrenderer = $rendererfactory->get_discussion_renderer($forum, $discussion, 3);

                            return $discussionrenderer->render($USER, $post, []);
                        }else{
                            return false;
                        }
                    }
                }
            }
        }

        return false;

    }

    /**
     * Get the course pattern datauri to show on a course card.
     *
     * The datauri is an encoded svg that can be passed as a url.
     * @param int $id Id to use when generating the pattern
     * @return string datauri
     */
    protected static function get_dummy_image_for_id($id) {
        $color = self::get_dummy_color_for_id($id);
        $pattern = new \core_geopattern();
        $pattern->setColor($color);
        $pattern->patternbyid($id);
        return $pattern->datauri();
    }

    /**
     * Get the course color to show on a course card.
     *
     * @param int $id Id to use when generating the color.
     * @return string hex color code.
     */
    protected static function get_dummy_color_for_id($id) {
        // The colour palette is hardcoded for now. It would make sense to combine it with theme settings.
        $basecolors = [
            '#81ecec',
            '#74b9ff',
            '#a29bfe',
            '#dfe6e9',
            '#00b894',
            '#0984e3',
            '#b2bec3',
            '#fdcb6e',
            '#fd79a8',
            '#6c5ce7'
        ];
        $color = $basecolors[$id % 10];
        return $color;
    }

    /**
     * Get first activity link for section
     *
     * @param $sectionnumber
     * @param $course
     * @return false|string
     * @throws \moodle_exception
     */
    protected static function get_section_activity_link ($sectionnumber, $course){

        // Get a list of all the activities in the course.
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        foreach ($modules as $module) {
            if($sectionnumber){
                if($sectionnumber==$module->sectionnum){
                    if ($module->uservisible && !$module->is_stealth() && !empty($module->url)) {
                        $linkurl = new \moodle_url($module->url);
                        return $linkurl->out(false);
                    }
                }
            }else{
                if ($module->uservisible && !$module->is_stealth() && !empty($module->url)) {
                    $linkurl = new \moodle_url($module->url);
                    return $linkurl->out(false);
                }
            }
        }
        return false;
    }

    /**
     * Get course continue activity link
     *
     * @param $course
     * @return array|false|string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected static function get_continue_course_activity_link ($course){
        $completioninfo = new \completion_info($course);
        $cancomplete = isloggedin() && !isguestuser();

        // Get a list of all the activities in the course.
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $modscompleted = [];
        $modsuncompleted = [];
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url) || $module->get_section_info()->section==0) {
                continue;
            }

            if ($cancomplete && $completioninfo->is_enabled($module) != COMPLETION_TRACKING_NONE) {
                $completiondata = $completioninfo->get_data($module, true);

                if ($completiondata->completionstate == COMPLETION_COMPLETE || $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {

                    $modscompleted[] = $module;
                }else{

                    $modsuncompleted[] = $module;
                }
            }
        }

        if(count($modscompleted)){
            if(count($modsuncompleted)){
                $firstuncompleted = $modsuncompleted[array_key_first($modsuncompleted)];
                if ($firstuncompleted->uservisible && !$firstuncompleted->is_stealth() && !empty($firstuncompleted->url)) {
                    $linkurl = new \moodle_url($firstuncompleted->url);
                    return $linkurl->out(false);
                }
            }else{
                return $modscompleted;
            }
        }

        return false;
    }

    /**
     * Get action button for course start/continue/completed
     *
     * @param $data
     * @param $course
     * @return void
     * @throws \coding_exception
     */
    protected function get_course_action_button($data, $course){

        if(!$continuecourse =  $this->get_continue_course_activity_link($course)){
            if($startcourseurl = $this->get_section_activity_link(1,$course)){
                $startcourse =\html_writer::link(
                    $startcourseurl,
                    get_string('startcourse', 'format_mintcampus'),
                    [
                        'id'=>'mintcampusstartcourse',
                        'aria-label' => get_string('startcourse', 'format_mintcampus') . ' Button',
                        'tabindex' => '0',
                        'class' => 'aabtn'
                    ]
                );
            }else{
                $startcourseurl = false;
                $startcourse = '';
            }
        }else{
            if(is_array($continuecourse)){
                $startcourseurl = true;
                $startcourse = \html_writer::div(
                    get_string('coursecompleted', 'format_mintcampus'),
                    '',
                    ['id'=>'mintcampusstartcourse']
                );
                $data->coursecompleted = true;
            }else{
                $startcourseurl = $continuecourse;
                $startcourse = \html_writer::link(
                    $startcourseurl,
                    get_string('continuecourse', 'format_mintcampus'),
                    [
                        'id'=>'mintcampusstartcourse',
                        'aria-label' => get_string('continuecourse', 'format_mintcampus') . ' Button',
                        'tabindex' => '0',
                        'class' => 'aabtn'
                    ]
                );
            }
        }

        $data->startcourse = $startcourse;
        $data->startcourseurl= $startcourseurl;
    }


}
