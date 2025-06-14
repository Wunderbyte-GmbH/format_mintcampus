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

function xmldb_format_mintcampus_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022072200) {
        // Define table format_mintcampus_image to be created.
        $table = new xmldb_table('format_mintcampus_image');
        $somethingbroke = false;

        // Has the script been executed already and broken?
        if ($dbman->table_exists($table)) {
            $somethingbroke = true;
            $dbman->drop_table($table);
        }

        // Adding fields to table format_mintcampus_image.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('image', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('contenthash', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('displayedimagestate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table format_mintcampus_image.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table format_mintcampus_image.
        $table->add_index('section', XMLDB_INDEX_UNIQUE, ['sectionid']);
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, ['courseid']);

        // Create table for format_mintcampus_image.
        $dbman->create_table($table);

        $lock = true;
        if (!defined('BEHAT_SITE_RUNNING')) {
            $lockfactory = \core\lock\lock_config::get_lock_factory('format_mintcampus');
            $lock = $lockfactory->get_lock('mintcampusupgradelock2022072200', 5);
        }
        if ($lock) {
            try {
                $oldtable = new xmldb_table('format_mintcampus_icon');
                if ($dbman->table_exists($oldtable)) {
                    // Upgrade from old images.
                    $oldimages = $DB->get_records('format_mintcampus_icon');
                    if (!empty($oldimages)) {
                        $newimages = [];
                        foreach ($oldimages as $oldimage) {
                            if (!empty($oldimage->image)) {
                                try {
                                    context_course::instance($oldimage->courseid);
                                } catch (\Exception $ex) {
                                    // Course does not exist for this image, skip.
                                    continue;
                                }

                                $newimagecontainer = new \stdClass();
                                $newimagecontainer->sectionid = $oldimage->sectionid;
                                $newimagecontainer->courseid = $oldimage->courseid;
                                $newimagecontainer->image = $oldimage->image;
                                $newimagecontainer->displayedimagestate = 0;
                                // Contenthash later!
                                $DB->insert_record('format_mintcampus_image', $newimagecontainer, true);
                                if (!array_key_exists($newimagecontainer->courseid, $newimages)) {
                                    $newimages[$newimagecontainer->courseid] = [];
                                }
                                $newimages[$newimagecontainer->courseid][$newimagecontainer->sectionid] = $newimagecontainer;
                            }
                        }

                        $fs = get_file_storage();
                        foreach ($newimages as $currentcourseid => $newimagecoursearray) {
                            $coursecontext = context_course::instance($currentcourseid);
                            $files = $fs->get_area_files($coursecontext->id, 'course', 'section');
                            foreach ($files as $file) {
                                if (!$file->is_directory()) {
                                    if ($file->get_filepath() == '/mintcampusimage/') {
                                        $file->delete();
                                    } else {
                                        $filename = $file->get_filename();
                                        $filesectionid = $file->get_itemid();
                                        if (array_key_exists($filesectionid, $newimagecoursearray)) { // Ensure we know about this section.
                                            $mintcampusimage = $newimagecoursearray[$filesectionid];
                                            if (($mintcampusimage) && ($mintcampusimage->image == $filename)) { // Ensure the correct file.
                                                $filerecord = new stdClass();
                                                $filerecord->contextid = $coursecontext->id;
                                                $filerecord->component = 'format_mintcampus';
                                                $filerecord->filearea = 'sectionimage';
                                                $filerecord->itemid = $filesectionid;
                                                $filerecord->filepath = '/';
                                                $filerecord->filename = $filename;
                                                $thefile = false;
                                                if ($somethingbroke) {
                                                    // Check to see if the file is already there.
                                                    $thefile = $fs->get_file(
                                                        $filerecord->contextid,
                                                        $filerecord->component,
                                                        $filerecord->filearea,
                                                        $filerecord->itemid,
                                                        $filerecord->filepath,
                                                        $filerecord->filename
                                                    );
                                                }
                                                if ($thefile === false) {
                                                    $thefile = $fs->create_file_from_storedfile($filerecord, $file);
                                                }
                                                if ($thefile !== false) {
                                                    $DB->set_field(
                                                        'format_mintcampus_image',
                                                        'contenthash',
                                                        $thefile->get_contenthash(),
                                                        ['sectionid' => $filesectionid]
                                                    );
                                                    // Don't delete the section file in case used in the summary.
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Delete 'format_mintcampus_icon' and 'format_mintcampus_summary' tables....
                $dbman->drop_table($oldtable);
                $oldsummarytable = new xmldb_table('format_mintcampus_summary');
                $dbman->drop_table($oldsummarytable);

                if (!defined('BEHAT_SITE_RUNNING')) {
                    $lock->release();
                }
            } catch (\Exception $e) {
                if (!defined('BEHAT_SITE_RUNNING')) {
                    $lock->release();
                }
                throw $e;
            }
        } else {
            throw new moodle_exception('cannotgetupgradelock', 'format_mintcampus', '', 'Cannot get upgrade lock');
        }

        // mintcampus savepoint reached.
        upgrade_plugin_savepoint(true, 2022072200, 'format', 'mintcampus');
    }

    // Automatic 'Purge all caches'....
    purge_all_caches();

    return true;
}
