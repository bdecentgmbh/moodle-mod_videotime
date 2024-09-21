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
 * @package     videotimetab_related
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_related;

use context_module;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_related
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
        if ($record = $DB->get_record('videotimetab_related', ['videotime' => $instance->id])) {
            $cm = get_coursemodule_from_instance('videotime', $instance->id, $instance->course);
            $context = context_module::instance($cm->id);
            $text = format_text(file_rewrite_pluginfile_urls(
                $record->text,
                'pluginfile.php',
                $context->id,
                'videotimetab_related',
                'text',
                0
            ), $record->format, [
                'noclean' => true,
            ]);

            $modinfo = get_fast_modinfo($instance->course);
            $relatedmods = [];
            foreach ($modinfo->cms as $mod) {
                if ($cm->section == $mod->section) {
                    $url = new moodle_url('/mod/' . $mod->modname . '/view.php', ['id' => $mod->id]);
                    $relatedmods[] = [
                        'current' => $cm->id == $mod->id,
                        'name' => $mod->name,
                        'iconurl' => $mod->get_icon_url(),
                        'url' => $url->out(false),
                    ];
                }
            }
            return $OUTPUT->render_from_template('videotimetab_related/tab_content', [
                'text' => $text,
                'related' => $relatedmods,
            ]);
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
            'advcheckbox',
            'enable_related',
            get_string('pluginname', 'videotimetab_related'),
            get_string('showtab', 'videotime')
        );
        $mform->setDefault('enable_related', get_config('videotimetab_related', 'default'));
        $mform->disabledIf('enable_related', 'enabletabs');

        $mform->addElement(
            'editor',
            'relatedinformation',
            get_string('relatedinformation', 'videotimetab_related'),
            ['rows' => 10],
            [
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'noclean' => true,
                'subdirs' => true,
            ]
        );
        $mform->setType('relatedinformation', PARAM_RAW);
        $mform->disabledIf('relatedinformation', 'enable_related');

        $mform->addElement('text', 'relatedtab_name', get_string('relatedtab_name', 'videotimetab_related'));
        $mform->setType('relatedtab_name', PARAM_TEXT);
        $mform->disabledIf('relatedtab_name', 'enable_related');
    }

    /**
     * Saves a settings in database
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_related)) {
            $DB->delete_records('videotimetab_related', [
                'videotime' => $data->id,
            ]);
        } else {
            $cm = get_coursemodule_from_instance('videotime', $data->id, $data->course);
            $text = file_save_draft_area_files(
                $data->relatedinformation['itemid'],
                context_module::instance($cm->id)->id,
                'videotimetab_related',
                'text',
                0,
                ['subdirs' => true],
                $data->relatedinformation['text']
            );
            if ($record = $DB->get_record('videotimetab_related', ['videotime' => $data->id])) {
                $record->text = $text;
                $record->format = $data->relatedinformation['format'];
                $record->name = $data->relatedtab_name;
                $DB->update_record('videotimetab_related', $record);
            } else {
                $DB->insert_record('videotimetab_related', [
                    'videotime' => $data->id,
                    'text' => $text,
                    'format' => $data->relatedinformation['format'],
                    'name' => $data->relatedtab_name,
                ]);
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

        $DB->delete_records('videotimetab_related', [
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
        global $COURSE, $DB;

        if (empty($instance)) {
            $defaultvalues['enable_related'] = get_config('videotimetab_related', 'default');
        } else if ($record = $DB->get_record('videotimetab_related', ['videotime' => $instance])) {
            $defaultvalues['enable_related'] = 1;
            $cm = get_coursemodule_from_instance('videotime', $record->videotime, $COURSE->id);
            $context = context_module::instance($cm->id);
            $draftitemid = file_get_submitted_draft_itemid('relatedinformation');
            $defaultvalues['relatedinformation'] = [
                'text' => file_prepare_draft_area(
                    $draftitemid,
                    $context->id,
                    'videotimetab_related',
                    'text',
                    0,
                    [],
                    $record->text
                ),
                'format' => $record->format,
                'itemid' => $draftitemid,
            ];
            $defaultvalues['relatedtab_name'] = $record->name;
        } else {
            $defaultvalues['enable_related'] = 0;
        }
    }

    /**
     * Report file areas for backup
     *
     * @return array
     */
    public static function get_config_file_areas(): array {
        return ['text'];
    }

    /**
     * Whether tab is enabled and visible
     *
     * @return bool
     */
    public function is_visible(): bool {
        global $DB;

        $record = $this->get_instance()->to_record();
        return $this->is_enabled() && $DB->record_exists('videotimetab_related', [
            'videotime' => $record->id,
        ]);
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
