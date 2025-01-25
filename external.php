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
 * Format MINT Campus External Class
 *
 * @package    format_mintcampus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/course/format/mintcampus/locallib.php');

use format_mintcampus\output\courseformat\content as contentclass;

/**
 * Class external.
 *
 * The external API for the Format MINT Campus.
 *
 * @package    format_mintcampus
 * @copyright  2017 Jun Pataleta

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_mintcampus_external extends external_api {
    /**
     * Parameter description for get_activity_navigation().
     *
     * @since Moodle 3.5
     * @return external_function_parameters
     */
    public static function get_activity_navigation_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'The cmid of module view', VALUE_OPTIONAL)
        ]);
    }

    /**
     * Get activity navigation in activity view, plus cmid of previous non-hidden activity
     *
     * @param $cmid
     * @return array|string[]
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function get_activity_navigation($cmid) {
        global $PAGE,$OUTPUT,$DB;
        $params = external_api::validate_parameters(self::get_activity_navigation_parameters(), [
            'cmid' => $cmid
        ]);
        $cmid = $params['cmid'];

        if($cmid==0){
            return  [
                'activityheader' => '',
                'activityfooter' => ''
            ];
        }

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);

        $PAGE->set_context(\context_system::instance());

        //$courseformat = course_get_format($course);

        $coursemodule = $DB->get_record('course_modules',['id'=>$cmid]);
        $courseid = $coursemodule->course;
        $sectionnum = $DB->get_field('course_sections','section',['id'=>$coursemodule->section,'course'=>$courseid]);;

        $course = get_course($courseid);
        // Get a list of all the activities in the course.
        $modules = get_fast_modinfo($courseid)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url) || $module->get_section_info()->section==0) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $cmid) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }
            // Module URL.
            $linkurl = new moodle_url($module->url, array('forceview' => 1));
            // Add module URL (as key) and name (as value) to the activity list array.
            $activitylist[$linkurl->out(false)] = $modname;
        }


        $nummods = count($mods);


        // If there is only one mod then do nothing.
        if (!$nummods == 0) {

            // Get an array of just the course module ids used to get the cmid value based on their position in the course.
            $modids = array_keys($mods);

            // Get the position in the array of the course module we are viewing.
            $position = array_search($cmid, $modids);

            $prevmod = null;
            $nextmod = null;

            // Check if we have a previous mod to show.
            if ($position > 0) {
                $prevmod = $mods[$modids[$position - 1]];
            }

            // Check if we have a next mod to show.
            if ($position < ($nummods - 1)) {
                $nextmod = $mods[$modids[$position + 1]];
            }

            $activitynav = new \core_course\output\activity_navigation($prevmod, $nextmod, $activitylist);

            $renderer = $PAGE->get_renderer('core', 'course');
            $activitynavigation = $renderer->render($activitynav);
        }
        //completionstates

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        $coursecompletion = format_mintcampus_section_completion_graphic(false, $course);
        $sectioncompletion = format_mintcampus_section_completion_graphic($sections[$sectionnum], $course);

//        $courserating = \html_writer::tag('i','',['id'=>'mintcampusrating','class'=>'fa fa-star-half-o fa-3','aria-hidden'=>'true']);
        $courserating = '';
        $starsnum = 5;
        for($i = 0; $i < $starsnum; $i++) {
            $courserating .= \html_writer::tag('i','',['id'=>'mintcampusrating','class'=>'fa fa-star-o fa-3','aria-hidden'=>'true']);
        }

        $headerdata = [
            'activitynavigation'=>$activitynavigation,
            'coursecompletionstate' => $coursecompletion,
            'sectioncompletionstate' => $sectioncompletion,
            'sectionnum'=> $sectionnum,
            'courserating'=> $courserating,
            'courseid' => $courseid
        ];


        $activitydata= [
            'activityheader' => $OUTPUT->render_from_template('format_mintcampus/activityheader', $headerdata),
            'activityfooter' => $OUTPUT->render_from_template('format_mintcampus/activityfooter', ['activitynavigation'=>$activitynavigation]),
        ];

        if($DB->get_record('format_mintcampus_menuitem',['courseid'=>$courseid,'cmid'=>$cmid]) && $prevmod){

            while ($DB->get_record('format_mintcampus_menuitem',['courseid'=>$courseid,'cmid'=>$prevmod->id]) || $prevmod==null){
                $position--;
                if ($position > 0) {
                    $prevmod = $mods[$modids[$position - 1]];
                }else{
                    $prevmod = null;
                }
            }
            $activitydata['previouscm'] = $prevmod <> null ? $prevmod->id : 0;

        }

        return $activitydata;
    }

    /**
     *
     * @return external_single_structure
     */
    public static function get_activity_navigation_returns() {
        return new external_single_structure(
            array(
                'activityheader' => new external_value(PARAM_RAW, 'Activity header',true),
                'activityfooter' => new external_value(PARAM_RAW, 'Activity footer',true),
                'previouscm' => new external_value(PARAM_INT, 'Previous ',false,0)), 'notification message'
        );
    }

    /**
     *
     * @return external_function_parameters
     */
    public static function add_rating_parameters() {
        return new external_function_parameters (
            array(
                'courseid'        => new external_value(PARAM_INT, 'associated id'),
                'rating'        => new external_value(PARAM_TEXT, 'user rating')
            )
        );
    }

    /**
     * @param $value
     * @param $itemid
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function add_rating($courseid, $rating) {
        global $DB,$USER, $CFG;

        $params = array(
            'courseid' => $courseid,
            'rating' => $rating
        );

        // Validate and normalize parameters.
        $params = self::validate_parameters(self::add_rating_parameters(), $params);

        $courseid = $params['courseid'];
        $rating = $params['rating'];

        if($ratingprompts=$DB->get_record('format_mintcampus_ratings',['courseid'=>$courseid, 'userid'=>$USER->id])){
            $ratingprompts->rating=$rating;
            $ratingprompts->timemodified=time();
            $DB->update_record('format_mintcampus_ratings',$ratingprompts);
        }else{
            $params['userid']=$USER->id;
            $params['timemodified']=time();
            $DB->insert_record('format_mintcampus_ratings',$params);
        }

        $returndata = array(
            'courseid' => $courseid,
            'rating' => $rating
        );

        return $returndata;
    }

    /**
     * Returns description of add_rating result values.
     *
     * @return external_single_structure
     * @since Moodle 3.2
     */
    public static function add_rating_returns() {
        return new external_single_structure(
            array(
                'courseid' => new external_value(PARAM_RAW, 'Course rating body',true),
                'rating' => new external_value(PARAM_RAW, 'Course rating body',true)), 'notification message'
        );
    }

    /**
     * Returns description of add_rating parameters.
     *
     * @return external_function_parameters
     * @since Moodle 3.2
     */
    public static function add_comment_parameters() {
        return new external_function_parameters (
            array(
                'courseid'        => new external_value(PARAM_INT, 'associated id'),
                'submission'        => new external_value(PARAM_TEXT, 'user comment')
            )
        );
    }

    /**
     * @param $comment
     * @param $promptitemid
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function add_comment($courseid, $submission) {
        global $USER,$DB;
        $params = array(
            'courseid' => $courseid,
            'submission' => $submission
        );

        // Validate and normalize parameters.
        $params = self::validate_parameters(self::add_comment_parameters(), $params);

        $courseid = $params['courseid'];
        $submission = $params['submission'];

        if($ratingprompts=$DB->get_record('format_mintcampus_comments',['courseid'=>$courseid,'userid'=>$USER->id])){
            $ratingprompts->submission=$submission;
            $ratingprompts->timemodified=time();
            $DB->update_record('format_mintcampus_comments',$ratingprompts);
        }else{
            $DB->insert_record('format_mintcampus_comments',['courseid'=>$courseid,
                'submission'=>$submission,
                'userid'=>$USER->id,'timemodified'=>time()]);
        }

        $returndata = array(
            'courseid' => $courseid,
            'submission' => $submission
        );

        return $returndata;
    }

    /**
     * Returns description of add_rating result values.
     *
     * @return external_single_structure
     * @since Moodle 3.2
     */
    public static function add_comment_returns() {
        return new external_single_structure(
            array(
                'courseid' => new external_value(PARAM_RAW, 'Course rating body',true),
                'submission' => new external_value(PARAM_RAW, 'Course rating body',true)), 'notification message'
        );
    }

    /**
     * Returns description of add_rating parameters.
     *
     * @return external_function_parameters
     * @since Moodle 3.2
     */
    public static function get_rating_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_REQUIRED)
        ]);
    }

    /**
     * @param $comment
     * @param $promptitemid
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_rating($courseid) {
        global $DB,$USER,$OUTPUT,$PAGE, $CFG;
        $params = array(
            'courseid' => $courseid,
        );
        $userid = $USER->id;
        // Validate and normalize parameters.
        $params = self::validate_parameters(self::get_rating_parameters(), $params);
        $courseid = $params['courseid'];

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);

        $PAGE->set_context(\context_system::instance());
        require_once($CFG->libdir . '/completionlib.php');
        $completion = new \completion_info(get_course($courseid));
        $activities = $completion->get_activities();
        $hascompleted = false;

        foreach ($activities as $cm) {
            $data = $completion->get_data($cm, true, $USER->id);
            if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) {
                $hascompleted = true;
                break;
            }
        }
        $completed = $hascompleted ? '' : '';

        $context = \context_course::instance($courseid);
        $ratings = [];
        if (has_capability('moodle/course:manage', $context, $userid) || is_siteadmin()) {
            $ratings = get_course_ratings_with_comments($courseid);
        }

        $data = [
            'header' => get_string('courserating','format_mintcampus'),
            'courseid' =>  $courseid,
            'question' => get_string('courseratingquestion','format_mintcampus'),
            'title'=> '',
            'comment' => get_string('courseratingcomment','format_mintcampus'),
            'placeholder' => get_string('placeholder','format_mintcampus'),
            'completionstatus' => \core_completion_external::get_course_completion_status($courseid, $USER->id)['completed'] ? 1 : null,
            'completed' => $completed,
            'ratings' => $ratings
        ];

        if($rating = $DB->get_record('format_mintcampus_ratings',['courseid'=>$courseid,'userid'=>$USER->id])){
            $data['selected'.$rating->rating] = true;
        }

        if($comment=$DB->get_record('format_mintcampus_comments',['courseid'=>$courseid, 'userid'=>$USER->id])){
            $data['submission']=$comment->submission;
        }else{
            $data['submission']='';
        }

        return ['ratingprompt' => $OUTPUT->render_from_template('format_mintcampus/rateprompt', $data)];
    }

    /**
     * Returns description of add_rating result values.
     *
     * @return external_single_structure
     * @since Moodle 3.2
     */
    public static function get_rating_returns() {
        return new external_single_structure(
            array(
                'ratingprompt' => new external_value(PARAM_RAW, 'Course rating body',true)), 'notification message'
        );
    }

    /**
     * Returns description of add_rating parameters.
     *
     * @return external_function_parameters
     * @since Moodle 3.2
     */
    public static function get_activity_setting_parameters() {
        return new external_function_parameters (
            array(
                'courseid' => new external_value(PARAM_INT, 'associated id')
            )
        );
    }

    /**
     * @param $value
     * @param $itemid
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_activity_setting($courseid) {
        global $DB;
        $params = array(
            'courseid' => $courseid
        );

        // Validate and normalize parameters.
        $params = self::validate_parameters(self::get_activity_setting_parameters(), $params);

        $returndata = [];
        $courseid = $params['courseid'];


        if($togglesettings=$DB->get_records('format_mintcampus_menuitem',['courseid'=>$courseid])){
            foreach ($togglesettings as $togglesetting){
                $returndata[]=['cmid' => $togglesetting->cmid];
            }
        }else{
            $returndata[]= ['cmid' => 0];
        }

        return $returndata;
    }

    /**
     * Returns description of add_rating result values.
     *
     * @return external_single_structure
     * @since Moodle 3.2
     */

    public static function get_activity_setting_returns() {
    return new external_multiple_structure(new external_single_structure([
        'cmid' => new external_value(PARAM_INT, 'The component for the icon.')
    ]));
    }

    /**
     * Returns description of add_rating parameters.
     *
     * @return external_function_parameters
     * @since Moodle 3.2
     */
    public static function set_activity_setting_parameters() {
        return new external_function_parameters (
            array(
                'courseid'        => new external_value(PARAM_INT, 'associated id'),
                'cmid'        => new external_value(PARAM_INT, 'associated id'),
                'state'        => new external_value(PARAM_BOOL, 'toggle state')
            )
        );
    }

    /**
     * @param $comment
     * @param $promptitemid
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function set_activity_setting($courseid, $cmid, $state) {
        global $USER,$DB;
        $params = array(
            'courseid' => $courseid,
            'cmid' => $cmid,
            'state' => $state
        );

        // Validate and normalize parameters.
        $params = self::validate_parameters(self::set_activity_setting_parameters(), $params);

        $courseid = $params['courseid'];
        $cmid = $params['cmid'];
        $state = $params['state'];

        if($state){
            $id = $DB->insert_record('format_mintcampus_menuitem',['courseid'=>$courseid,
                'cmid'=>$cmid,
                'userid'=>$USER->id,'timemodified'=>time()]);
        }else{
            $state = $DB->delete_records('format_mintcampus_menuitem',['courseid'=>$courseid,
                'cmid'=>$cmid]);
        }

        $returndata = array(
            'id' => $id,
            'state' => $state
        );

        return $returndata;
    }

    /**
     * Returns description of add_rating result values.
     *
     * @return external_single_structure
     * @since Moodle 3.2
     */
    public static function set_activity_setting_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'toggle id',true),
                'state' => new external_value(PARAM_BOOL, 'State',true)), 'notification message'
        );
    }

    /**
     * Returns description of delete_rating parameters.
     *
     * @return external_function_parameters
     * @since Moodle 3.2
     */
    public static function delete_rating_parameters() {
        return new external_function_parameters (
            array(
                'ratingid' => new external_value(PARAM_INT, 'The ID of the rating to be deleted.')
            )
        );
    }

    /**
     * Deletes a rating entry from the database.
     *
     * @param int $ratingid The ID of the rating to delete.
     * @return array Status message of deletion.
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function delete_rating($ratingid) {
        global $DB;

        // Validate and normalize parameters.
        $params = self::validate_parameters(self::delete_rating_parameters(), ['ratingid' => $ratingid]);

        $ratingid = $params['ratingid'];

        // Check if the rating exists.
        if ($DB->record_exists('format_mintcampus_ratings', ['id' => $ratingid])) {
            $DB->delete_records('format_mintcampus_ratings', ['id' => $ratingid]);
            $response = ['status' => 'success', 'message' => 'Rating deleted successfully.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Rating not found.'];
        }

        return $response;
    }

    /**
     * Returns description of delete_rating result values.
     *
     * @return external_single_structure
     * @since Moodle 3.2
     */
    public static function delete_rating_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the delete operation.'),
            'message' => new external_value(PARAM_TEXT, 'Message describing the operation result.')
        ]);
    }
}
