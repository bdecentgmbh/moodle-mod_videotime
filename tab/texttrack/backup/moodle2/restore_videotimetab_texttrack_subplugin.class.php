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
 * videotime restore task
 *
 * provides all the settings and steps to perform one * complete restore of the activity
 *
 * @package     videotimetab_texttrack
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotime/backup/moodle2/restore_videotime_stepslib.php'); // Because it exists (must).

/**
 * Define restore step for videotime tab plugin
 *
 * restore subplugin class that provides the data
 * needed to restore one videotimetab_texttrack subplugin.
 */
class restore_videotimetab_texttrack_subplugin extends restore_subplugin {

    /**
     * Define subplugin structure
     *
     */
    protected function define_videotime_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('');
        $elepath = $this->get_pathfor('/videotimetab_texttrack');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    /**
     * Processes the videotimetab_texttrack element, if it is in the file.
     * @param array $data the data read from the XML file.
     */
    public function process_videotimetab_texttrack($data) {
        global $DB;

        $data = (object)$data;
        $oldvideotime = $data->videotime;
        $data->videotime = $this->get_new_parentid('videotime');
        $DB->insert_record('videotimetab_texttrack', $data);

    }
}
