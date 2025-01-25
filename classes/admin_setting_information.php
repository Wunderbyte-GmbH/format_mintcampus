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

namespace format_mintcampus;

/**
 * Setting that displays information.  Based on admin_setting_description in adminlib.php.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class admin_setting_information extends \admin_setting {

    /** @var int The branch this is for. */
    protected $mbranch;

    /**
     * Not a setting, just information.
     *
     * @param string $name Setting name.
     * @param string $visiblename Setting name on the device.
     * @param string $description Setting description on the device.
     * @param string $mbranch The branch this is for.
     */
    public function __construct($name, $visiblename, $description, $mbranch) {
        $this->nosave = true;
        $this->mbranch = $mbranch;
        return parent::__construct($name, $visiblename, $description, '');
    }

    /**
     * Always returns true.
     *
     * @return bool Always returns true.
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true.
     *
     * @return bool Always returns true.
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Never write settings
     *
     * @param mixed $data Gets converted to str for comparison against yes value.
     * @return string Always returns an empty string.
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Returns an HTML string
     *
     * @param string $data
     * @param string $query
     * @return string Returns an HTML string
     */
    public function output_html($data, $query='') {
        global $CFG, $OUTPUT;

        $formats = \core_plugin_manager::instance()->get_present_plugins('format');
        if (!empty($formats['mintcampus'])) {
            $plugininfo = $formats['mintcampus'];
        } else {
            $plugininfo = \core_plugin_manager::instance()->get_plugin_info('format_mintcampus');
            $plugininfo->version = $plugininfo->versiondisk;
        }

        $classes[] = 'fa fa-heart';
        $attributes = array();
        $attributes['aria-hidden'] = 'true';
        $attributes['class'] = 'fa fa-heart';
        $attributes['title'] = get_string('love', 'format_mintcampus');
        $content = \html_writer::tag('span', $attributes['title'], array('class' => 'sr-only'));
        $content = \html_writer::tag('span', $content, $attributes);
        $context['versioninfo'] = get_string('versioninfo', 'format_mintcampus',
            array(
                'moodle' => $CFG->release,
                'release' => $plugininfo->release,
                'version' => $plugininfo->version,
                'love' => $content
            )
        );

        if (!empty($plugininfo->maturity)) {
            switch ($plugininfo->maturity) {
                case MATURITY_ALPHA:
                    $context['maturity'] = get_string('versionalpha', 'format_mintcampus');
                    $context['maturityalert'] = 'danger';
                break;
                case MATURITY_BETA:
                    $context['maturity'] = get_string('versionbeta', 'format_mintcampus');
                    $context['maturityalert'] = 'danger';
                break;
                case MATURITY_RC:
                    $context['maturity'] = get_string('versionrc', 'format_mintcampus');
                    $context['maturityalert'] = 'warning';
                break;
                case MATURITY_STABLE:
                    $context['maturity'] = get_string('versionstable', 'format_mintcampus');
                    $context['maturityalert'] = 'info';
                break;
            }
        }

        if ($CFG->branch != $this->mbranch) {
            $context['versioncheck'] = 'Release '.$plugininfo->release.', version '.$plugininfo->version;
            $context['versioncheck'] .= ' is incompatible with Moodle '.$CFG->release;
        }

        return $OUTPUT->render_from_template('format_mintcampus/mintcampus_admin_setting_information', $context);
    }
}
