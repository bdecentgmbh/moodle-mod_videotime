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
 * @package     videotimetab_information
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_information;

use context_module;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_information
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab name for ids
     *
     * @return string
     */
    public function get_name(): string {
        return 'information';
    }

    /**
     * Get label for tab
     *
     * @return string
     */
    public function get_label(): string {
        return get_string('tabinformation', 'videotime');
    }

    /**
     * Get tab panel content
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $DB;

        $instance = $this->get_instance();
        if ($record = $DB->get_record('videotimetab_information', array('videotime' => $instance->id))) {
            $cm = get_coursemodule_from_instance('videotime', $instance->id, $instance->course);
            $context = context_module::instance($cm->id);
            return format_text(file_rewrite_pluginfile_urls(
                $record->text,
                'pluginfile.php',
                $context->id,
                'videotimetab_information',
                'text',
                0
            ), $record->format);
        } else {
            return '';
        }
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
        $mform->addElement(
            'editor',
            'information',
            get_string('information', 'videotimetab_information'),
            array('rows' => 10),
            array(
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'noclean' => true,
                'subdirs' => true,
            )
        );
        $mform->setType('information', PARAM_RAW);
        $mform->disabledIf('information', 'enabletabs');
    }

    /**
     * Saves a settings in database
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->information['text'])) {
            $DB->delete_records('videotimetab_information', array(
                'videotime' => $data->id,
            ));
        } else {
            $cm = get_coursemodule_from_instance('videotime', $data->id, $data->course);
            $text = file_save_draft_area_files(
                $data->information['itemid'],
                context_module::instance($cm->id)->id,
                'videotimetab_information',
                'text',
                0,
                array('subdirs' => true),
                $data->information['text']
            );
            if ($record = $DB->get_record('videotimetab_information', array('videotime' => $data->id))) {
                $record->text = $text;
                $record->format = $data->information['format'];
                $DB->update_record('videotimetab_information', $record);
            } else {
                $DB->insert_record('videotimetab_information', array(
                    'videotime' => $data->id,
                    'text' => $text,
                    'format' => $data->information['format'],
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

        $DB->delete_records('videotimetab_information', array(
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

        if ($record = $DB->get_record('videotimetab_information', array('videotime' => $instance))) {
            $cm = get_coursemodule_from_instance('videotime', $record->videotime, $COURSE->id);
            $context = context_module::instance($cm->id);
            $draftitemid = file_get_submitted_draft_itemid('information');
            $defaultvalues['information'] = array(
                'text' => file_prepare_draft_area($draftitemid, $context->id,
                    'videotimetab_information', 'text', 0, [], $record->text),
                'format' => $record->format,
                'itemid' => $draftitemid,
            );
        }
    }

    /**
     * Report file areas for backup
     *
     * @return array
     */
    public static function get_config_file_areas(): array {
        return array('text');
    }
}
