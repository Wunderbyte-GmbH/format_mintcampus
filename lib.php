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

use format_mintcampus\output\courseformat\content;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.

class format_mintcampus extends core_courseformat\base {
    // Used to determine the type of view URL to generate - parameter or anchor.
    private $coursedisplay = COURSE_DISPLAY_MULTIPAGE;

    private $settings = null;

    /**
     * Creates a new instance of class
     *
     * Please use {@link course_get_format($courseorid)} to get an instance of the format class
     *
     * @param string $format
     * @param int $courseid
     * @return format_mintcampus
     */
    protected function __construct($format, $courseid) {
        global $PAGE;
        parent::__construct($format, $courseid);
        if ($courseid === 0) {
            global $COURSE;
            $courseid = $COURSE->id;  // Save lots of global $COURSE as we will never be the site course.
        }
        parent::__construct($format, $courseid);
    }

    /**
     * Get the course display value for the current course.
     *
     * @return int The current value (COURSE_DISPLAY_MULTIPAGE or COURSE_DISPLAY_SINGLEPAGE).
     */
    public function get_course_display(): int {
        return $this->coursedisplay;
    }

    /**
     * Returns the format's settings and gets them if they do not exist.
     * @param bool $invalidate Invalidate the existing known settings and get a fresh set.  Set when you know the settings have
     *                         changed.
     * @return array The settings as an array.
     */
    public function get_settings($invalidate = false) {
        if ($invalidate) {
            $this->settings = null;
        }
        if (empty($this->settings) == true) {
            $this->settings = $this->get_format_options();
            foreach ($this->settings as $settingname => $settingvalue) {
                if (isset($settingvalue)) {
                    $settingvtype = gettype($settingvalue);
                    if ((($settingvtype == 'string') && ($settingvalue === '-')) ||
                        (($settingvtype == 'integer') && ($settingvalue === 0))) {
                        // Default value indicator is a hyphen or a number equal to 0.
                        $this->settings[$settingname] = get_config('format_mintcampus', 'default'.$settingname);
                    }
                }
            }

            $this->settings['mintcampuscoursevideo_filemanager'] = $this->get_mintcampuscoursevideo_filemanager();
            $this->settings['mintcampuscourseimage_filemanager'] = $this->get_mintcampuscourseimage_filemanager();
        }
        return $this->settings;
    }

    /**
     * Returns true if this course format uses sections.
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    public function uses_course_index() {
        return true;
    }

    public function uses_indentation(): bool {
        return false;
    }

    /**
     * Gets the name for the provided section.
     *
     * @param stdClass $section The section.
     * @return string The section name.
     */
    public function get_section_name($section) {
        $thesection = $this->get_section($section);
        if ((string)$thesection->name !== '') {
            return format_string($thesection->name, true,
                ['context' => context_course::instance($this->courseid)]);
        } else {
            return $this->get_default_section_name($thesection);
        }
    }


    /**
     * Returns the default section name for the topics course format.
     *
     * If the section number is 0, it will use the string with key = section0name from the course format's lang file.
     * If the section number is not 0, the base implementation of course_format::get_default_section_name which uses
     * the string with the key = 'sectionname' from the course format's lang file + the section number will be used.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            // Return the general section.
            return get_string('section0name', 'format_mintcampus');
        } else {
            // Use course_format::get_default_section_name implementation which will display the section name in "Topic n" format.
            return parent::get_default_section_name($section);
        }
    }

    /**
     * Generate the title for this section page.
     *
     * @return string the page title
     */
    public function page_title(): string {
        return get_string('topicoutline');
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', ['id' => $course->id]);

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $this->coursedisplay;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (empty($CFG->linkcoursesections) && !empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-'.$sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    public function supports_components() {
        return true;
    }

    /**
     * Loads all of the course sections into the navigation.
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     * @return void
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE,$COURSE;

        $cmid = optional_param('id',0,PARAM_INT);
        if($cmid<>0){
            $PAGE->requires->js_call_amd('format_mintcampus/activitynavigation', 'init',array($cmid));
        }

        // If section is specified in course/view.php, make sure it is expanded in navigation.
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // Check if there are callbacks to extend course navigation.
        parent::extend_course_navigation($navigation, $node);

        // We want to remove the general section if it is empty.
        $modinfo = get_fast_modinfo($this->get_course());
        $sections = $modinfo->get_sections();

        if (!isset($sections[0])) {
            // The general section is empty to find the navigation node for it we need to get its ID.
            $section = $modinfo->get_section_info(0);
            $generalsection = $node->get($section->id, navigation_node::TYPE_SECTION);
            if ($generalsection) {
                // We found the node - now remove it.
                $generalsection->remove();
            }
        }
    }

    /**
     * Custom action after section has been moved in AJAX mode.
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = [];
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return ['sectiontitles' => $titles, 'action' => 'move'];
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course.
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return [
            BLOCK_POS_LEFT => [],
            BLOCK_POS_RIGHT => [],
        ];
    }

    /**
     * Definitions of the additional options that this course format uses for the course.
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        $courseconfig = null;

        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseid = $this->get_courseid();
            if ($courseid == 1) { // New course.
                $defaultnumsections = $courseconfig->numsections;
            } else { // Existing course that may not have 'numsections' - see get_last_section().
                global $DB;
                $defaultnumsections = $DB->get_field_sql('SELECT max(section) from {course_sections}
                    WHERE course = ?', array($courseid));
            }
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $defaultnumsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => 1,
                    'type' => PARAM_INT
                ),
                'mintcampuscoursevideo_filemanager' => array(
                    'default' => false,
                    'type' => PARAM_INT
                ),
                'mintcampuscourseimage_filemanager' => array(
                    'default' => false,
                    'type' => PARAM_INT
                )
            );
        }
        if ($foreditform && !isset($courseformatoptions['numsections']['label'])) {
            if (is_null($courseconfig)) {
                $courseconfig = get_config('moodlecourse');
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $courseconfig->maxsections; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'mintcampuscoursevideo_filemanager' => array(
                    'label' => new lang_string('mintcampuscoursevideo_filemanager', 'format_mintcampus'),
                    'element_type' => 'filemanager',
                    'element_attributes' => [[], array(
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => array('.mov','.mp4')
                    )],
                    'help' => 'mintcampuscoursevideo_filemanager',
                    'help_component' => 'format_mintcampus',
                ),'mintcampuscourseimage_filemanager' => array(
                    'label' => new lang_string('mintcampuscourseimage_filemanager', 'format_mintcampus'),
                    'element_type' => 'filemanager',
                    'element_attributes' => [[], array(
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => array('.png', '.jpg')
                    )],
                    'help' => 'mintcampuscourseimage_filemanager',
                    'help_component' => 'format_mintcampus',
                ),
                'numsections' => array(
                    'label' => new lang_string('numbersections', 'format_mintcampus'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'hidden',
                    'element_attributes' => array(
                        array(1 => new lang_string('hiddensectionsinvisible'))
                    ),
                )
            );

            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Generates the default setting value entry.
     *
     * @param string $settingname Setting name.
     * @param string/int $defaultindex Default index.
     * @param array $values Setting value array to add the default entry to.
     * @return array Updated value array with the added default entry.
     */
    private function generate_default_entry($settingname, $defaultindex, $values) {
        $defaultvalue = get_config('format_mintcampus', 'default'.$settingname);
        $defarray = array($defaultindex => new lang_string('default', 'format_mintcampus', $values[$defaultvalue]));

        return array_replace($defarray, $values);
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $CFG, $USER;
        MoodleQuickForm::registerElementType(
            'sectionfilemanager',
            "$CFG->dirroot/course/format/mintcampus/form/sectionfilemanager.php",
            'MoodleQuickForm_sectionfilemanager');

        $elements = parent::create_edit_form_elements($mform, $forsection);

        /* Increase the number of sections combo box values if the user has increased the number of sections
           using the icon on the course page beyond course 'maxsections' or course 'maxsections' has been
           reduced below the number of sections already set for the course on the site administration course
           defaults page.  This is so that the number of sections is not reduced leaving unintended orphaned
           activities / resources. */
        if (!$forsection) {
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ($numsections > $maxsections) {
                $element = $mform->getElement('numsections');
                for ($i = $maxsections + 1; $i <= $numsections; $i++) {
                    $element->addOption("$i", $i);
                }
                array_unshift($elements, $element);
            }
        }


        //course video
        $fs = get_file_storage();
        $coursecontext = context_course::instance($this->courseid);
        $usercontext = context_user::instance($USER->id);

        $data = new stdClass;
        $fileitemid = $this->get_mintcampuscoursevideo_filemanager();
        $fs->delete_area_files($usercontext->id, 'user', 'draft', $this->courseid);
        $data = file_prepare_standard_filemanager(
            $data,
            'mintcampuscoursevideo',
            array('accepted_types' => ['.mov','.mp4'], 'maxfiles' => 1),
            $coursecontext,
            'format_mintcampus',
            'mintcampuscoursevideo_filearea',
            $fileitemid
        );

        $mform->setDefault('mintcampuscoursevideo_filemanager', $data->mintcampuscoursevideo_filemanager);
        foreach ($elements as $key => $element) {
            if ($element->getName() == 'mintcampuscoursevideo_filemanager') {
                $element->setMaxfiles(1);
            }
        }

        //course image
        $fs = get_file_storage();

        $data = new stdClass;
        $fileitemid = $this->get_mintcampuscourseimage_filemanager();
        $fs->delete_area_files($usercontext->id, 'user', 'draft', $this->courseid);
        $data = file_prepare_standard_filemanager(
            $data,
            'mintcampuscourseimage',
            array('accepted_types' => ['.png','.jpg'], 'maxfiles' => 1),
            $coursecontext,
            'format_mintcampus',
            'mintcampuscourseimage_filearea',
            $fileitemid
        );

        $mform->setDefault('mintcampuscourseimage_filemanager', $data->mintcampuscourseimage_filemanager);
        foreach ($elements as $key => $element) {
            if ($element->getName() == 'mintcampuscourseimage_filemanager') {
                $element->setMaxfiles(1);
            }
        }


        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'mintcampus', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * The layout and colour defaults will come from 'course_format_options'.
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data.
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update.
     * @return bool whether there were any changes to the options values.
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB; // MDL-37976.

        $currentsettings = $this->get_settings();
        $data = (array) $data;
        if ($oldcourse !== null) {
            $oldcourse = (array) $oldcourse;
            $options = $this->course_format_options();

            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        /* If previous format does not have the field 'numsections' and $data['numsections'] is not set,
                           we fill it with the maximum section number from the DB. */
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($this->courseid));
                        if ($maxsection) {
                            // If there are no sections, or just default 0-section, 'numsections' will be set to default.
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        $data = (object) $data;
        if (!isset($data->mintcampuscoursevideo_filemanager)) {
            $data->mintcampuscoursevideo_filemanager = '';
        }
        if (!isset($data->mintcampuscourseimage_filemanager)) {
            $data->mintcampuscourseimage_filemanager = '';
        }

        if (!empty($data)) {

            //course video
            // Used optional_param() instead of using $_POST and $_GET.
            $contextid = context_course::instance($this->courseid);
            if (!empty($data->mintcampuscoursevideo_filemanager)) {
                file_postupdate_standard_filemanager(
                    $data,
                    'mintcampuscoursevideo',
                    array ('accepted_types' => ['.mov','.mp4'], 'maxfiles' => 1),
                    $contextid,
                    'format_mintcampus',
                    'mintcampuscoursevideo_filearea',
                    $data->mintcampuscoursevideo_filemanager
                );
            }

            $this->set_mintcampuscoursevideo_filemanager($data->mintcampuscoursevideo_filemanager);

            //course image
            if (!empty($data->mintcampuscourseimage_filemanager)) {
                file_postupdate_standard_filemanager(
                    $data,
                    'mintcampuscourseimage',
                    array ('accepted_types' => ['.png','.jpg'], 'maxfiles' => 1),
                    $contextid,
                    'format_mintcampus',
                    'mintcampuscourseimage_filearea',
                    $data->mintcampuscourseimage_filemanager
                );
            }

            $this->set_mintcampuscourseimage_filemanager($data->mintcampuscourseimage_filemanager);

        }
        $data = (array) $data;

        $changes = $this->update_format_options($data);

        if ($changes && array_key_exists('numsections', $data)) {
            // If the numsections was decreased, try to completely delete the orphaned sections (unless they are not empty).
            $numsections = (int)$data['numsections'];
            $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                WHERE course = ?', array($this->courseid));
            for ($sectionnum = $maxsection; $sectionnum > $numsections; $sectionnum--) {
                if (!$this->delete_section($sectionnum, false)) {
                    break;
                }
            }
        }

        return $changes;
    }

    public function section_format_options($foreditform = false) {
        static $sectionformatoptions = false;
        if ($sectionformatoptions === false) {
            $sectionformatoptions = array(
                'sectionimage_filemanager' => array(
                    'default' => '',
                    'type' => PARAM_RAW
                ),
                'sectionimagealttext' => array(
                    'default' => '',
                    'type' => PARAM_TEXT
                ),
                'sectionbreak' => array(
                    'default' => 1, // No.
                    'type' => PARAM_INT
                ),
                'sectionbreakheading' => array(
                    'default' => '',
                    'type' => PARAM_RAW
                )
            );
        }

        // Adding fields for course contents replacing summary
        for ($i = 1; $i <= content::MODULE_CONTENTS_NUM; $i++) {
            $fieldname = content::MODULE_CONTENT_FIELD_NAME_BASE . $i;
            $sectionformatoptions[$fieldname] = array(
                'default' => '',
                'type' => PARAM_TEXT
            );
        }
        if ($foreditform && !isset($sectionformatoptions['sectionimage_filemanager']['label'])) {
            $sectionformatoptionsedit = array(
                'sectionimage_filemanager' => array(
                    'label' => new lang_string('sectionimage', 'format_mintcampus'),
                    'help' => 'sectionimage',
                    'help_component' => 'format_mintcampus',
                    'element_type' => 'sectionfilemanager',
                    'element_attributes' => array(
                        array(
                            'course' => $this->course,
                            'sectionid' => optional_param('id', 0, PARAM_INT)
                        )
                    )
                ),
                'sectionimagealttext' => array(
                    'label' => new lang_string('sectionimagealttext', 'format_mintcampus'),
                    'help' => 'sectionimagealttext',
                    'help_component' => 'format_mintcampus',
                    'element_type' => 'text'
                ),
                'sectionbreak' => array(
                    'label' => new lang_string('sectionbreak', 'format_mintcampus'),
                    'help' => 'sectionbreak',
                    'help_component' => 'format_mintcampus',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('no'),
                            2 => new lang_string('yes')
                        )
                    ),
                ),
                'sectionbreakheading' => array(
                    'label' => new lang_string('sectionbreakheading', 'format_mintcampus'),
                    'help' => 'sectionbreakheading',
                    'help_component' => 'format_mintcampus',
                    'element_type' => 'textarea'
                )
            );

            // Adding fields for course contents replacing summary
            for ($i = 1; $i <= content::MODULE_CONTENTS_NUM; $i++) {
                $fieldname = content::MODULE_CONTENT_FIELD_NAME_BASE . $i;
                $sectionformatoptionsedit[$fieldname] = array(
                    'label' => new lang_string($fieldname, 'format_mintcampus'),
                    'element_type' => 'text',
                );
            }

            $sectionformatoptions = array_merge_recursive($sectionformatoptions, $sectionformatoptionsedit);
        }

        return $sectionformatoptions;
    }

    /**
     * Deletes a section
     *
     * Do not call this function directly, instead call {@link course_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @param bool $forcedeleteifnotempty if set to false section will not be deleted if it has modules in it.
     * @return bool whether section was deleted
     */
    public function delete_section($section, $forcedeleteifnotempty = false) {
        if (!$this->uses_sections()) {
            // Not possible to delete section if sections are not used.
            return false;
        }
        if (!is_object($section)) {
            global $DB;
            $section = $DB->get_record('course_sections', array('course' => $this->get_courseid(), 'section' => $section),
                'id,section,sequence,summary');
        }
        if (!$section || !$section->section) {
            // Not possible to delete 0-section.
            return false;
        }

        if (!$forcedeleteifnotempty && (!empty($section->sequence) || !empty($section->summary))) {
            return false;
        }
        if (parent::delete_section($section, $forcedeleteifnotempty)) {
            \format_mintcampus\toolbox::delete_image($section->id, $this->get_courseid());
            return true;
        }
        return false;
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Prepares the templateable object to display section name.
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
            $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_mintcampus');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'format_mintcampus', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

    /**
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0, not allow in orphaned sections.
        return !$section->section || ($section->visible && $section->section <= $this->get_course()->numsections);
    }

    public function section_action($section, $action, $sr) {
        global $PAGE;

        if ($section->section && ($action === 'setmarker' || $action === 'removemarker')) {
            // Format 'topics' allows to set and remove markers in addition to common section actions.
            require_capability('moodle/course:setcurrentsection', context_course::instance($this->courseid));
            course_set_marker($this->courseid, ($action === 'setmarker') ? $section->section : 0);
            return null;
        }

        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $rv = parent::section_action($section, $action, $sr);
        $renderer = $PAGE->get_renderer('format_mintcampus');

        if (!($section instanceof section_info)) {
            $modinfo = course_modinfo::instance($this->courseid);
            $section = $modinfo->get_section_info($section->section);
        }
        $elementclass = $this->get_output_classname('content\\section\\availability');
        $availability = new $elementclass($this, $section);

        $rv['section_availability'] = $renderer->render($availability);
        return $rv;
    }

    /**
     * Restores the numsections if was not in the backup.
     * @param int $numsections The number of sections.
     */
    public function restore_numsections($numsections) {
        $data = array('numsections' => $numsections);
        $this->update_course_format_options($data);
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     * @since Moodle 3.5
     */
    public function get_config_for_external() {
        // Return everything (nothing to hide).
        return $this->get_format_options();
    }

    // mintcampus specific methods...
    /**
     * Class instance update images callback.
     */
    public static function update_displayed_images_callback() {
        \format_mintcampus\toolbox::update_displayed_images_callback();
    }

    /**
     * DB value setter for mintcampuscoursevideo_filemanager option
     * @return int Item id
     */
    public function get_mintcampuscoursevideo_filemanager() {
        global $DB;
        $itemid = $DB->get_field('course_format_options', 'value', array(
            'courseid' => $this->courseid,
            'format' => 'mintcampusformat',
            'sectionid' => 0,
            'name' => 'mintcampuscoursevideo_filemanager'
        ));
        if (!$itemid) {
            $itemid = file_get_unused_draft_itemid();
        }
        return $itemid;
    }

    /**
     * DB value setter for mintcampuscoursevideo_filemanager option
     * @param boolean $itemid Image itemid
     */
    public function set_mintcampuscoursevideo_filemanager($itemid = false) {
        global $DB;
        $courseimage = $DB->get_record('course_format_options', array(
            'courseid' => $this->courseid,
            'format' => 'mintcampusformat',
            'sectionid' => 0,
            'name' => 'mintcampuscoursevideo_filemanager'
        ));
        if ($courseimage == false) {
            $courseimage = (object) array(
                'courseid' => $this->courseid,
                'format' => 'mintcampusformat',
                'sectionid' => 0,
                'name' => 'mintcampuscoursevideo_filemanager'
            );
            $courseimage->id = $DB->insert_record('course_format_options', $courseimage);
        }
        $courseimage->value = $itemid;
        $DB->update_record('course_format_options', $courseimage);
        return true;
    }

    /**
     * DB value setter for mintcampuscoursevideo_filemanager option
     * @return int Item id
     */
    public function get_mintcampuscourseimage_filemanager() {
        global $DB;
        $itemid = $DB->get_field('course_format_options', 'value', array(
            'courseid' => $this->courseid,
            'format' => 'mintcampusformat',
            'sectionid' => 0,
            'name' => 'mintcampuscourseimage_filemanager'
        ));
        if (!$itemid) {
            $itemid = file_get_unused_draft_itemid();
        }
        return $itemid;
    }

    /**
     * DB value setter for mintcampuscoursevideo_filemanager option
     * @param boolean $itemid Image itemid
     */
    public function set_mintcampuscourseimage_filemanager($itemid = false) {
        global $DB;
        $courseimage = $DB->get_record('course_format_options', array(
            'courseid' => $this->courseid,
            'format' => 'mintcampusformat',
            'sectionid' => 0,
            'name' => 'mintcampuscourseimage_filemanager'
        ));
        if ($courseimage == false) {
            $courseimage = (object) array(
                'courseid' => $this->courseid,
                'format' => 'mintcampusformat',
                'sectionid' => 0,
                'name' => 'mintcampuscourseimage_filemanager'
            );
            $courseimage->id = $DB->insert_record('course_format_options', $courseimage);
        }
        $courseimage->value = $itemid;
        $DB->update_record('course_format_options', $courseimage);
        return true;
    }

    /**
     * Course-specific information to be output immediately above content on any course page
     *
     * See course_format::course_header() for usage
     *
     * @return null|renderable null for no output or object with data for plugin renderer
     */
    public function course_content_header() {
        //TODO navigation, section completion, course completion, rating
        return new format_mintcampus_header();
    }

    /**
     * Course-specific information to be output immediately below content on any course page
     *
     * See course_format::course_header() for usage
     *
     * @return null|renderable null for no output or object with data for plugin renderer
     */
    public function course_content_footer() {
        //TODO navigation
        return new format_mintcampus_footer();
    }
}

/**
 * Class storing information to be displayed in course header/footer
 *
 * @package    format_testheaders
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_mintcampus_header implements renderable {}

/**
 * Class storing information to be displayed in course header/footer
 *
 * @package    format_testheaders
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_mintcampus_footer implements renderable {}

// Transposed from block_html_pluginfile.
/**
 * Form for editing HTML block instances.
 *
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   format_mintcampus
 * @param stdClass $course course object
 * @param stdClass $birecordorcm block instance record
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function format_mintcampus_pluginfile($course, $birecordorcm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($context->contextlevel != CONTEXT_COURSE) {
        send_file_not_found();
    }

    // Check if user has capability to access course.
    require_course_login($course);

    if ($filearea == 'displayedsectionimage'){

        $fs = get_file_storage();

        $filename = $args[2];
        $sectionid = $args[0];

        $file = $fs->get_file($context->id, 'format_mintcampus', 'displayedsectionimage', $sectionid, '/', $filename);
        if (!$file || $file->is_directory()) {
            send_file_not_found();
        }

        // NOTE:
        // It would be nice to have file revisions here, for now rely on standard file lifetime,
        // do not lower it because the files are displayed very often.  But... mintcampus format is using
        // displayedsectionimage in the URL as a means to overcome this.
        \core\session\manager::write_close();
        send_stored_file($file, null, 0, $forcedownload, $options);
    }elseif($filearea == 'mintcampuscourseimage_filearea' || $filearea = 'mintcampuscoursevideo_filearea'){

        $itemid = (int)array_shift($args);
        $fs = get_file_storage();
        $filename = array_pop($args);

        if (empty($args)) {
            $filepath = '/';
        } else {
            $filepath = '/'.implode('/', $args).'/';
        }
        $file = $fs->get_file($context->id, 'format_mintcampus', $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false;
        }
        send_stored_file($file, 0, 0, 0, $options);
    }else{
        send_file_not_found();
    }

}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_mintcampus_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            [$itemid, 'mintcampus'], MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}

/**
 * Get Thumbnail Image
 *
 * @param $context
 * @return string
 * @throws coding_exception
 */
function format_mintcampus_get_video($context) {
    global $COURSE;
    $fileitemid = format_mintcampus_coursevideo_filemanager($COURSE->id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'format_mintcampus', 'mintcampuscoursevideo_filearea', $fileitemid);
    if ($files) {
        foreach ($files as $file) {
            $url = moodle_url::make_pluginfile_url($context->id, 'format_mintcampus', 'mintcampuscoursevideo_filearea', $fileitemid, $file->get_filepath(),
                $file->get_filename(), false);
        }
        return $url;
    } else {
        return false;
    }
}

/**
 * DB value setter for mintcampuscoursevideo_filemanager option
 * @return int Item id
 */
function format_mintcampus_coursevideo_filemanager($courseid) {
    global $DB;
    $itemid = $DB->get_field('course_format_options', 'value', array(
        'courseid' => $courseid,
        'format' => 'mintcampusformat',
        'sectionid' => 0,
        'name' => 'mintcampuscoursevideo_filemanager'
    ));
    if (!$itemid) {
        $itemid = file_get_unused_draft_itemid();
    }
    return $itemid;
}

/**
 * Get Thumbnail Image
 *
 * @param $context
 * @return string
 * @throws coding_exception
 */
function format_mintcampus_get_image($context) {
    global $COURSE;
    $fileitemid = format_mintcampus_courseimage_filemanager($COURSE->id);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'format_mintcampus', 'mintcampuscourseimage_filearea', $fileitemid);
    if ($files) {
        foreach ($files as $file) {
            $url = moodle_url::make_pluginfile_url($context->id, 'format_mintcampus', 'mintcampuscourseimage_filearea', $fileitemid, $file->get_filepath(),
            $file->get_filename(), false);
        }
        return $url;
    } else {
        return false;
    }
}

/**
 * DB value setter for mintcampuscoursevideo_filemanager option
 * @return int Item id
 */
function format_mintcampus_courseimage_filemanager($courseid) {
    global $DB;
    $itemid = $DB->get_field('course_format_options', 'value', array(
        'courseid' => $courseid,
        'format' => 'mintcampusformat',
        'sectionid' => 0,
        'name' => 'mintcampuscourseimage_filemanager'
    ));
    if (!$itemid) {
        $itemid = file_get_unused_draft_itemid();
    }
    return $itemid;
}
