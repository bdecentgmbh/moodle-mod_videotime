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
 * @package mod_label
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_url_activity_task
 */

/**
 * Structure step to restore one label activity
 */
class restore_videotime_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        global $CFG;

        require_once($CFG->dirroot.'/mod/videotime/lib.php');

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('videotime', '/activity/videotime');
        if ($userinfo && videotime_has_pro()) {
            $paths[] = new restore_path_element('videotime_session', '/activity/videotime/sessions/session');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_videotime($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.

        // insert the label record
        $newitemid = $DB->insert_record('videotime', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_videotime_session($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->module_id = $this->get_mappingid('course_module', $data->module_id);

        $newitemid = $DB->insert_record('videotime_session', $data);
        $this->set_mapping('videotime_session', $oldid, $newitemid);
    }

    protected function after_execute() {
        // Add label related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_videotime', 'intro', null);
        $this->add_related_files('mod_videotime', 'video_description', null);
        $this->add_related_files('mod_videotime', 'preview_image', null);
    }

}
