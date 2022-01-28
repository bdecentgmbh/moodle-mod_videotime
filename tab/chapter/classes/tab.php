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
 * @package     videotimetab_chapter
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_chapter;

use context_module;
use mod_videotime\output\renderer;
use mod_videotime\videotime_instance;
use videotimeplugin_repository\video;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_chapter
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab panel content
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $DB, $OUTPUT, $PAGE, $USER;

        $instance = $this->get_instance();
        $data = [
            'id' => $instance->id,
            'title' => $instance->name,
        ];

        return $OUTPUT->render_from_template(
            'videotimetab_chapter/tab',
            $data
        );
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
        $mform->addElement('advcheckbox', 'enable_chapter', get_string('pluginname', 'videotimetab_chapter'),
            get_string('showtab', 'videotime'));
        $mform->setDefault('enable_chapter', get_config('videotimetab_chapter', 'default'));
        $mform->disabledIf('enable_chapter', 'enabletabs');
    }

    /**
     * Whether tab is enabled and visible
     *
     * @return bool
     */
    public function is_visible(): bool {
        global $DB;

        $record = $this->get_instance()->to_record();
        return $this->is_enabled() && $DB->record_exists('videotimetab_chapter', array(
            'videotime' => $record->id
        ));
    }

    /**
     * Saves a settings in database
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_chapter)) {
            $DB->delete_records('videotimetab_chapter', array(
                'videotime' => $data->id,
            ));
        } else {
            if (!$record = $DB->get_record('videotimetab_chapter', array('videotime' => $data->id))) {
                $DB->insert_record('videotimetab_chapter', array(
                    'videotime' => $data->id,
                ));
            }
        }
    }

    /**
     * Delete settings in database
     *
     * @param  int $id
     */
    public static function delete_settings(int $id) {
        global $DB;

        $DB->delete_records('videotimetab_chapter', array(
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
        global $COURSE, $DB;

        if (empty($instance)) {
            $defaultvalues['enable_chapter'] = get_config('videotimetab_chapter', 'default');
        } else if ($record = $DB->get_record('videotimetab_chapter', array('videotime' => $instance))) {
            $defaultvalues['enable_chapter'] = 1;
        } else {
            $defaultvalues['enable_chapter'] = 0;
        }
    }

    /**
     * List of missing dependencies needed for plugin to be enabled
     */
    public static function added_dependencies() {
        global $OUTPUT;
        if (videotime_has_pro()) {
            return '';
        }
        return $OUTPUT->render_from_template('videotimetab_chapter/upgrade', []);
    }
}
