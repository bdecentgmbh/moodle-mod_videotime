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
 * @package     videotimetab_resources
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_resources;

use context_module;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Resources tab — displays teacher-uploaded files in the Video Time tab interface.
 *
 * @package videotimetab_resources
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab panel content.
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $OUTPUT, $PAGE;

        $instance = $this->get_instance();
        $cm       = get_coursemodule_from_instance('videotime', $instance->id, $instance->course);
        $context  = context_module::instance($cm->id);
        $fs       = get_file_storage();

        $storedfiles = $fs->get_area_files(
            $context->id,
            'videotimetab_resources',
            'files',
            0,
            'sortorder, itemid, filepath, filename',
            false
        );

        $filelist = [];
        foreach ($storedfiles as $file) {
            $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                'videotimetab_resources',
                'files',
                0,
                $file->get_filepath(),
                $file->get_filename(),
                true
            );
            $ext = strtoupper(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
            $filelist[] = [
                'name'      => $file->get_filename(),
                'url'       => $url->out(false),
                'size'      => display_size($file->get_filesize()),
                'extension' => $ext,
                'mimetype'  => $file->get_mimetype(),
                'icon'      => $OUTPUT->image_url(file_mimetype_icon($file->get_mimetype()))->out(false),
            ];
        }

        $canedit = has_capability('moodle/course:manageactivities', $context);
        $editurl = '';
        if ($canedit) {
            $url = new moodle_url('/course/modedit.php', ['update' => $cm->id, 'return' => 1]);
            $url->set_anchor('fitem_id_enable_resources');
            $editurl = $url->out(false);
            $PAGE->requires->js_call_amd('videotimetab_resources/editresources', 'init', [$cm->id]);
        }

        return $OUTPUT->render_from_template('videotimetab_resources/tab', [
            'filelist' => $filelist,
            'hasfiles' => !empty($filelist),
            'canedit'  => $canedit,
            'editurl'  => $editurl,
            'cmid'     => $cm->id,
        ]);
    }

    /**
     * Whether tab is enabled and visible.
     *
     * The tab is hidden when no files have been uploaded yet, so students
     * never see an empty resources list.
     *
     * @return bool
     */
    public function is_visible(): bool {
        global $DB;

        if (!$this->is_enabled()) {
            return false;
        }

        $record = $this->get_instance()->to_record();

        if (!$DB->record_exists('videotimetab_resources', ['videotime' => $record->id])) {
            return false;
        }

        $cm      = get_coursemodule_from_instance('videotime', $record->id, $record->course);
        $context = context_module::instance($cm->id);
        $fs      = get_file_storage();

        // Returns false when no (non-directory) files exist.
        $files = $fs->get_area_files($context->id, 'videotimetab_resources', 'files', 0, 'id', false, 0, 0, 1);
        return !empty($files);
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
        $mform->addElement(
            'advcheckbox',
            'enable_resources',
            get_string('pluginname', 'videotimetab_resources'),
            get_string('showtab', 'videotime')
        );
        $mform->setDefault('enable_resources', get_config('videotimetab_resources', 'default'));
        $mform->disabledIf('enable_resources', 'enabletabs');

        $mform->addElement(
            'filemanager',
            'resources',
            get_string('resources', 'videotimetab_resources'),
            null,
            [
                'subdirs'      => 0,
                'maxfiles'     => -1,
                'return_types' => \FILE_INTERNAL,
            ]
        );
        $mform->hideIf('resources', 'enable_resources', 'notchecked');

        $mform->addElement('text', 'resourcestab_name', get_string('resourcestab_name', 'videotimetab_resources'));
        $mform->setType('resourcestab_name', PARAM_TEXT);
        $mform->hideIf('resourcestab_name', 'enable_resources', 'notchecked');

        $mform->addElement('static', 'tab_separator_resources', '', '<hr class="mt-1 mb-2">');
        $mform->hideIf('tab_separator_resources', 'enable_resources', 'notchecked');
    }

    /**
     * Saves settings in database.
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_resources)) {
            $DB->delete_records('videotimetab_resources', ['videotime' => $data->id]);
        } else {
            $cm      = get_coursemodule_from_instance('videotime', $data->id, $data->course);
            $context = context_module::instance($cm->id);

            file_save_draft_area_files(
                $data->resources,
                $context->id,
                'videotimetab_resources',
                'files',
                0,
                ['subdirs' => 0]
            );

            if ($record = $DB->get_record('videotimetab_resources', ['videotime' => $data->id])) {
                $record->name = $data->resourcestab_name ?? '';
                $DB->update_record('videotimetab_resources', $record);
            } else {
                $DB->insert_record('videotimetab_resources', [
                    'videotime' => $data->id,
                    'name'      => $data->resourcestab_name ?? '',
                ]);
            }
        }
    }

    /**
     * Delete settings in database.
     *
     * @param int $id videotime instance id
     */
    public static function delete_settings(int $id) {
        global $DB;

        $DB->delete_records('videotimetab_resources', ['videotime' => $id]);
    }

    /**
     * Prepares the form before data are set.
     *
     * @param array $defaultvalues
     * @param int $instance
     */
    public static function data_preprocessing(array &$defaultvalues, int $instance) {
        global $COURSE, $DB;

        if (empty($instance)) {
            $defaultvalues['enable_resources'] = get_config('videotimetab_resources', 'default');
            // Prepare an empty draft area for the filemanager.
            $draftitemid = file_get_submitted_draft_itemid('resources');
            file_prepare_draft_area($draftitemid, null, 'videotimetab_resources', 'files', 0);
            $defaultvalues['resources'] = $draftitemid;
        } else if ($record = $DB->get_record('videotimetab_resources', ['videotime' => $instance])) {
            $defaultvalues['enable_resources']  = 1;
            $defaultvalues['resourcestab_name'] = $record->name ?? '';

            $cm      = get_coursemodule_from_instance('videotime', $record->videotime, $COURSE->id);
            $context = context_module::instance($cm->id);

            $draftitemid = file_get_submitted_draft_itemid('resources');
            file_prepare_draft_area($draftitemid, $context->id, 'videotimetab_resources', 'files', 0);
            $defaultvalues['resources'] = $draftitemid;
        } else {
            $defaultvalues['enable_resources'] = 0;
            $draftitemid = file_get_submitted_draft_itemid('resources');
            file_prepare_draft_area($draftitemid, null, 'videotimetab_resources', 'files', 0);
            $defaultvalues['resources'] = $draftitemid;
        }
    }

    /**
     * Report file areas for backup.
     *
     * @return array
     */
    public static function get_config_file_areas(): array {
        return ['files'];
    }

    /**
     * Get label for tab.
     *
     * @return string
     */
    public function get_label(): string {
        if ($label = $this->get_record()->name ?? '') {
            return $label;
        }

        return parent::get_label();
    }
}
