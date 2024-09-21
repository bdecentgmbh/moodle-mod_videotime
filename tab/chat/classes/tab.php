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
 * @package     videotimetab_chat
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_chat;

defined('MOODLE_INTERNAL') || die();

use block_contents;
use context_module;
use core_component;
use stdClass;

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_chat
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
        if ($record = $DB->get_record('videotimetab_chat', ['videotime' => $instance->id])) {
            $cm = get_coursemodule_from_instance('videotime', $instance->id, $instance->course);
            $context = context_module::instance($cm->id);
            $main = new output\main($context, $record);

            return $OUTPUT->render($main);
        }
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
        $mform->addElement(
            'advcheckbox',
            'enable_chat',
            get_string('pluginname', 'videotimetab_chat'),
            get_string('showtab', 'videotime')
        );
        $mform->setDefault('enable_chat', get_config('videotimetab_chat', 'default'));
        $mform->disabledIf('enable_chat', 'enabletabs');

        $mform->addElement('text', 'chattab_name', get_string('chattab_name', 'videotimetab_chat'));
        $mform->setType('chattab_name', PARAM_TEXT);
    }

    /**
     * Saves a settings in database
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_chat)) {
            $DB->delete_records('videotimetab_chat', [
                'videotime' => $data->id,
            ]);
        } else if ($record = $DB->get_record('videotimetab_chat', ['videotime' => $data->id])) {
            $record->name = $data->chattab_name;
            $DB->update_record('videotimetab_chat', $record);
        } else {
            $DB->insert_record('videotimetab_chat', [
                'videotime' => $data->id,
                'name' => $data->chattab_name,
            ]);
        }
    }

    /**
     * Delete settings in database
     *
     * @param  int $id
     */
    public static function delete_settings(int $id) {
        global $DB;

        $DB->delete_records('videotimetab_chat', [
            'videotime' => $id,
        ]);
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
            $defaultvalues['enable_chat'] = get_config('videotimetab_chat', 'default');
        } else {
            $defaultvalues['enable_chat'] = $DB->record_exists('videotimetab_chat', ['videotime' => $instance]);
        }
        if (empty($instance)) {
            $defaultvalues['enable_chat'] = get_config('videotimetab_chat', 'default');
        } else if ($record = $DB->get_record('videotimetab_chat', ['videotime' => $instance])) {
            $defaultvalues['enable_chat'] = 1;
            $defaultvalues['chattab_name'] = $record->name;
        } else {
            $defaultvalues['enable_chat'] = 0;
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
        return $this->is_enabled() && $DB->record_exists('videotimetab_chat', [
            'videotime' => $record->id,
        ]);
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
        return $OUTPUT->render_from_template('videotimetab_chat/upgrade', []);
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
