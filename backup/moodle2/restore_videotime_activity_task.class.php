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
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotime/backup/moodle2/restore_videotime_stepslib.php'); // Because it exists (must).

/**
 * videotime restore task
 *
 * provides all the settings and steps to perform one * complete restore of the activity
 */
class restore_videotime_activity_task extends restore_activity_task {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Videotime only has one structure step.
        $this->add_step(new restore_videotime_activity_structure_step('videotime_structure', 'videotime.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('videotime', ['intro'], 'videotime');
        $contents[] = new restore_decode_content('videotime', ['video_description'], 'videotime');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        return [];
    }

    /**
     * Define the restore log rules that will be applied by the link restore_logs_processor when restoring
     * videotime logs. It must return one array of restore_log_rule objects
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('videotime', 'add', 'view.php?id={course_module}', '{videotime}');
        $rules[] = new restore_log_rule('videotime', 'update', 'view.php?id={course_module}', '{videotime}');
        $rules[] = new restore_log_rule('videotime', 'view', 'view.php?id={course_module}', '{videotime}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied by the restore_logs_processor when restoring
     * course logs. It must return one array of restore_log_rule objects
     *
     * Note this rules are applied when restoring course logs by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        $rules[] = new restore_log_rule('videotime', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

    /**
     * Re-map the dependency and activitylink information
     * If a depency or activitylink has no mapping in the backup data then it could either be a duplication of a
     * lesson, or a backup/restore of a single lesson. We have no way to determine which and whether this is the
     * same site and/or course. Therefore we try and retrieve a mapping, but fallback to the original value if one
     * was not found. We then test to see whether the value found is valid for the course being restored into.
     */
    public function after_restore() {
        global $DB;

        if (!videotime_has_pro()) {
            return;
        }

        $options = $DB->get_record_sql(
            "SELECT p.id, v.course, p.next_activity_id
               FROM {videotime} v
               JOIN {videotimeplugin_pro} p ON p.videotime = v.id
              WHERE v.id = :id",
            ['id' => $this->get_activityid()]
        );
        $updaterequired = false;

        if ($options->next_activity_id > 0) {
            $updaterequired = true;
            if ($newitem = restore_dbops::get_backup_ids_record(
                $this->get_restoreid(),
                'course_module',
                $options->next_activity_id
            )) {
                $options->next_activity_id = $newitem->newitemid;
            }
            if (!$DB->record_exists('course_modules', [
                'id' => $options->next_activity_id,
                'course' => $options->course,
            ])) {
                $options->next_activity_id = 0;
            }
        }

        if ($updaterequired) {
            $DB->update_record('videotimeplugin_pro', $options);
        }
    }
}
