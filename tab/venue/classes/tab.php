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
 * Tab.
 *
 * @package     videotimetab_venue
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_venue;

defined('MOODLE_INTERNAL') || die();

use block_contents;
use context_module;
use core_component;
use stdClass;

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_venue
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab panel content
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $DB, $OUTPUT;

        $instance = $this->get_instance();
        if ($record = $DB->get_record('videotimetab_venue', array('videotime' => $instance->id))) {
            $main = new output\main($instance);

            return $OUTPUT->render($main);
        }
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
        $mform->addElement('advcheckbox', 'enable_venue', get_string('pluginname', 'videotimetab_venue'),
            get_string('showtab', 'videotime'));
        $mform->setDefault('enable_venue', get_config('videotimetab_venue', 'default'));
        $mform->disabledIf('enable_venue', 'enabletabs');

        $mform->addElement('text', 'venuetab_name', get_string('venuetab_name', 'videotimetab_venue'));
        $mform->setType('venuetab_name', PARAM_TEXT);
    }

    /**
     * Saves a settings in database
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_venue)) {
            $DB->delete_records('videotimetab_venue', array(
                'videotime' => $data->id,
            ));
        } else if ($record = $DB->get_record('videotimetab_venue', array('videotime' => $data->id))) {
            $record->name = $data->venuetab_name;
            $DB->update_record('videotimetab_venue', $record);
        } else {
            $DB->insert_record('videotimetab_venue', array(
                'videotime' => $data->id,
                'name' => $data->venuetab_name,
            ));
        }
    }

    /**
     * Delete settings in database
     *
     * @param  int $id
     */
    public static function delete_settings(int $id) {
        global $DB;

        $DB->delete_records('videotimetab_venue', array(
            'videotime' => $id,
        ));
    }

    /**
     * Prepares the form before data are set
     *
     * @param  array $defaultvalues
     * @param  int $instance
     */
    public static function data_preprocessing(array &$defaultvalues, int $instance) {
        global $DB;

        if (empty($instance)) {
            $defaultvalues['enable_venue'] = get_config('videotimetab_venue', 'default');
        } else {
            $defaultvalues['enable_venue'] = $DB->record_exists('videotimetab_venue', array('videotime' => $instance));
        }
        if (empty($instance)) {
            $defaultvalues['enable_venue'] = get_config('videotimetab_venue', 'default');
        } else if ($record = $DB->get_record('videotimetab_venue', array('videotime' => $instance))) {
            $defaultvalues['enable_venue'] = 1;
            $defaultvalues['venuetab_name'] = $record->name;
        } else {
            $defaultvalues['enable_venue'] = 0;
        }
    }

    /**
     * Whether tab is enabled and visible
     *
     * @return bool
     */
    public function is_visible(): bool {
        global $DB;

        $record = $this->get_instance()->to_record();
        return $this->is_enabled() && $DB->record_exists('videotimetab_venue', array(
            'videotime' => $record->id
        ));
    }

    /**
     * List of missing dependencies needed for plugin to be enabled
     */
    public static function added_dependencies() {
        global $OUTPUT;
        $manager = \core_plugin_manager::instance();
        $plugin = $manager->get_plugin_info('block_deft');
        if ($plugin && $plugin->versiondb > 2022111400) {
            return '';
        }
        return $OUTPUT->render_from_template('videotimetab_venue/upgrade', []);
    }

    /**
     * Get label for tab
     *
     * @return string
     */
    public function get_label(): string {
        if ($label = $this->get_record()->name) {
            return $label;
        }

        return parent::get_label();
    }
}
