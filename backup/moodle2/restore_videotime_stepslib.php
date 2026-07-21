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
class restore_videotime_activity_structure_step extends restore_questions_activity_structure_step {
    /** @var stdClass|null $currentquizattempt Track the current attempt being restored. */
    protected $currentattempt = null;

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        global $CFG;

        require_once($CFG->dirroot . '/mod/videotime/lib.php');

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $videotime = new restore_path_element('videotime', '/activity/videotime');
        $paths[] = $videotime;

        $texttrack = new restore_path_element('texttrack', '/activity/videotime/texttracks/texttrack');
        $paths[] = $texttrack;

        // A chance for tab subplugins to set up their data.
        $this->add_subplugin_structure('videotimetab', $videotime);
        $this->add_subplugin_structure('videotimeplugin', $videotime);

        if (class_exists('\videotimetab_interaction\tab')) {
             $videotimequestioninstance = new restore_path_element(
                 'videotime_question_instance',
                 '/activity/videotime/question_instances/question_instance'
             );
             $paths[] = $videotimequestioninstance;
             $this->add_question_references($videotimequestioninstance, $paths);
             $this->add_question_set_references($videotimequestioninstance, $paths);
        }

        if ($userinfo) {
            $paths[] = new restore_path_element('videotime_session', '/activity/videotime/sessions/session');

            if (class_exists('\videotimetab_interaction\tab')) {
                 $attempt = new restore_path_element(
                     'videotime_attempt',
                     '/activity/videotime/attempts/attempt'
                 );
                 $paths[] = $attempt;

                 // Add states and sessions.
                 $this->add_question_usages($attempt, $paths);
            }
        }

        $paths[] = new restore_path_element('vimeooptions', '/activity/videotime/vimeo_options');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Add annotated subplugin files
     * @param string $subtype the plugin type to handle
     * @return void
     */
    protected function add_subplugin_files($subtype) {
        $pluginmanager = new \mod_videotime\plugin_manager($subtype);
        $plugins = $pluginmanager->get_sorted_plugins_list();
        foreach ($plugins as $plugin) {
            $component = $subtype . '_' . $plugin;
            if ($subtype == 'videotimetab') {
                $classname = '\\' . $component . '\\tab';
                $areas = $classname::get_config_file_areas();
            } else {
                $areas = component_callback($component, 'config_file_areas', [], []);
            }
            foreach ($areas as $area) {
                $this->add_related_files($component, $area, null);
            }
        }
    }

    /**
     * Processes the videotime restore data.
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
     * Processes the texttrack restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_texttrack($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->videotime = $this->get_new_parentid('videotime');

        // Insert the videotime record.
        $newitemid = $DB->insert_record('videotime_track', $data);
        $this->set_mapping('videotime_track', $oldid, $newitemid, true);
    }

    /**
     * Process quiz slots.
     *
     * @param stdClass|array $data
     */
    protected function process_videotime_question_instance($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->cueid = $this->get_mappingid('videotimetab_interaction_cue', $data->cueid);

        // Backwards compatibility for old field names (MDL-43670).
        if (!isset($data->questionid) && isset($data->question)) {
            $data->questionid = $data->question;
        }

        $data->videotime = $this->get_new_parentid('videotime');

        $newitemid = $DB->insert_record('videotimetab_interaction_question', $data);

        // Add mapping, restore of slot tags (for random questions) need it.
        $this->set_mapping('videotime_question_instance', $oldid, $newitemid);
    }


    /**
     * Process attempt data
     *
     * @param array $data data
     */
    protected function process_videotime_attempt($data) {
        global $DB;

        $data = (object)$data;
        $data->videotime = $this->get_new_parentid('videotime');
        $data->userid = $this->get_mappingid('user', $data->userid);

        // The data is actually inserted into the database later in inform_new_usage_id.
        $this->currentattempt = clone($data);
    }


    /**
     * Process session data
     *
     * @param array $data data
     */
    protected function process_videotime_session($data) {
        global $DB;

        $data = (object)$data;

        $data->module_id = $this->get_mappingid('course_module', $data->module_id);
        $data->user_id = $this->get_mappingid('user', $data->user_id);

        $newitemid = $DB->insert_record('videotimeplugin_pro_session', $data);
    }

    /**
     * Process vimeo embed data
     *
     * @param array $data data
     */
    protected function process_vimeooptions($data) {
        global $DB;

        $data = (object)$data;
        $data->videotime = $this->get_new_parentid('videotime');
    }

    /**
     * Process question references which replaces the direct connection to quiz slots to question.
     *
     * @param array $data the data from the XML file.
     */
    public function process_question_reference($data) {
        global $DB;

        $data = (object) $data;
        $data->usingcontextid = $this->get_mappingid('context', $data->usingcontextid);
        $data->itemid = $this->get_new_parentid('videotime_question_instance');
        if ($entry = $this->get_mappingid('question_bank_entry', $data->questionbankentryid)) {
            $data->questionbankentryid = $entry;
        }
        $DB->insert_record('question_references', $data);
    }

    /**
     * Record new question usage
     *
     * @param int $newusageid New usage id
     */
    protected function inform_new_usage_id($newusageid) {
        global $DB;

        $data = $this->currentattempt;
        if ($data === null) {
            return;
        }

        $oldid = $data->id;
        $data->qubaid = $newusageid;

        $newitemid = $DB->insert_record('videotimeplugin_pro_attempt', $data);

        // Save quiz_attempt->id mapping, because logs use it.
        $this->set_mapping('videotime_attempt', $oldid, $newitemid, false);
    }

    /**
     * Defines post-execution actions to dd files
     */
    protected function after_execute() {
        global $DB;

        // Add videotime related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_videotime', 'intro', null);
        $this->add_related_files('mod_videotime', 'video_description', null);
        $this->add_related_files('mod_videotime', 'texttrack', 'videotime_track');

        $this->add_subplugin_files('videotimeplugin');

        $records = $DB->get_records_sql(
            "SELECT qr.itemid AS id, MAX(qv.questionid) AS questionid, iq.cueid
               FROM {question_versions} qv
               JOIN {question_references} qr ON qv.questionbankentryid = qr.questionbankentryid
               JOIN {videotimetab_interaction_question} iq ON iq.id = qr.itemid
              WHERE qr.usingcontextid = :contextid
                    AND qr.component = 'mod_videotime'
                    AND qr.questionarea = 'slot'
           GROUP BY qr.itemid, iq.cueid",
            ['contextid' => $this->task->get_contextid()]
        );
        foreach ($records as $record) {
            $DB->update_record('videotimetab_interaction_question', $record);
            $DB->update_record('videotimetab_interaction_cue', [
                'id' => $record->cueid,
                'data' => $record->questionid,
            ]);
        }
    }
}
