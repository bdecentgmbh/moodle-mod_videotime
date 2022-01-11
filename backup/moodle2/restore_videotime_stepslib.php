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
 * Structure step to restore one videotime activity
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one videotime activity
 *
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_videotime_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        global $CFG;

        require_once($CFG->dirroot.'/mod/videotime/lib.php');

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $videotime = new restore_path_element('videotime', '/activity/videotime');
        $paths[] = $videotime;

        // A chance for tab subplugins to set up their data.
        $this->add_subplugin_structure('videotimetab', $videotime);

        if ($userinfo && videotime_has_pro()) {
            $paths[] = new restore_path_element('videotime_session', '/activity/videotime/sessions/session');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the videotim restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_videotime($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.

        // Insert the videotime record.
        $newitemid = $DB->insert_record('videotime', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process session data
     *
     * @param array $data data
     */
    protected function process_videotime_session($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->module_id = $this->get_mappingid('course_module', $data->module_id);

        $newitemid = $DB->insert_record('videotime_session', $data);
        $this->set_mapping('videotime_session', $oldid, $newitemid);
    }

    /**
     * Defines post-execution actions to dd files
     */
    protected function after_execute() {
        // Add videotime related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_videotime', 'intro', null);
        $this->add_related_files('mod_videotime', 'video_description', null);
    }
}
