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
 * Dynamic form for editing resource files from within the tab.
 *
 * @package     videotimetab_resources
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_resources\form;

use context_module;
use mod_videotime\videotime_instance;
use moodle_url;
use videotimetab_resources\tab;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->dirroot/lib/filelib.php");
require_once("$CFG->dirroot/repository/lib.php");

/**
 * Dynamic form that lets teachers manage resource files without a full page reload.
 *
 * Opened via the JS ModalForm and submitted via AJAX. Returns the updated tab HTML
 * so the caller can swap it into the DOM.
 *
 * @package videotimetab_resources
 */
class editfiles_form extends \core_form\dynamic_form {

    /**
     * Return the context the form operates in.
     *
     * @return \context
     */
    protected function get_context_for_dynamic_submission(): \context {
        $cmid = $this->optional_param('cmid', 0, PARAM_INT);
        return context_module::instance($cmid);
    }

    /**
     * Return the URL Moodle should redirect to after a non-AJAX submission.
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $cmid = $this->optional_param('cmid', 0, PARAM_INT);
        return new moodle_url('/course/modedit.php', ['update' => $cmid, 'return' => 1]);
    }

    /**
     * Enforce that only users who can manage the activity may use this form.
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('moodle/course:manageactivities', $this->get_context_for_dynamic_submission());
    }

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement(
            'filemanager',
            'resources',
            get_string('resources', 'videotimetab_resources'),
            null,
            [
                'subdirs'      => 0,
                'maxfiles'     => -1,
                'return_types' => FILE_INTERNAL,
            ]
        );
    }

    /**
     * Populate the form with existing files by preparing a draft area.
     */
    public function set_data_for_dynamic_submission(): void {
        $cmid    = $this->optional_param('cmid', 0, PARAM_INT);
        $context = context_module::instance($cmid);

        $draftitemid = file_get_submitted_draft_itemid('resources');
        file_prepare_draft_area($draftitemid, $context->id, 'videotimetab_resources', 'files', 0);

        $this->set_data(['cmid' => $cmid, 'resources' => $draftitemid]);
    }

    /**
     * Save the uploaded files and return updated tab HTML for the JS to swap in.
     *
     * @return array containing 'html' key with the re-rendered tab content
     */
    public function process_dynamic_submission(): array {
        global $DB;

        $data    = $this->get_data();
        $cmid    = (int) $data->cmid;
        $cm      = get_coursemodule_from_id('videotime', $cmid);
        $context = context_module::instance($cmid);

        file_save_draft_area_files(
            $data->resources,
            $context->id,
            'videotimetab_resources',
            'files',
            0,
            ['subdirs' => 0]
        );

        // Ensure a DB record exists so is_visible() and get_tab_content() work.
        if (!$DB->record_exists('videotimetab_resources', ['videotime' => $cm->instance])) {
            $DB->insert_record('videotimetab_resources', ['videotime' => $cm->instance, 'name' => '']);
        }

        $instance = videotime_instance::instance_by_id($cm->instance);
        $tab      = new tab($instance);
        return ['html' => $tab->get_tab_content()];
    }
}
