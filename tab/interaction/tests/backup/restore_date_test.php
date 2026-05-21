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

namespace videotimetab_interaction\backup;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . "/phpunit/classes/restore_date_testcase.php");

/**
 * Restore date tests.
 *
 * @package    videotimetab_interaction
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \backup_videotime_activity_structure_step
 * @covers     \backup_videotimetab_interaction_subplugin
 * @covers     \restore_videotime_activity_structure_step
 * @covers     \restore_videotimetab_interaction_subplugin
 * @group      mod_videotime
 * @group      videotimetab_interaction
 */
final class restore_date_test extends \restore_date_testcase {
    /**
     * Test restore dates.
     */
    public function test_restore_dates(): void {
        global $DB;

        // Create videotime data.
        $record = [
            'enabletabs' => true,
            'enable_interaction' => true,
            'timemodified' => 100,
            'timeopen' => time(),
            'timeclose' => time() + DAYSECS,
        ];
        [$course, $videotime] = $this->create_course_and_module('videotime', $record);

        // Do backup and restore.
        $newcourseid = $this->backup_and_restore($course);
        $newvideotime = $DB->get_record_sql(
            "SELECT v.*, i.interval
               FROM {videotimetab_interaction} i
               JOIN {videotime} v ON v.id = i.videotime
              WHERE v.course = :course",
            ['course' => $newcourseid]
        );
        $oldvideotime = $DB->get_record_sql(
            "SELECT v.*, i.interval
               FROM {videotimetab_interaction} i
               JOIN {videotime} v ON v.id = i.videotime
              WHERE v.id = :id",
            ['id' => $videotime->id]
        );
        $this->assertNotEmpty($newvideotime);

        $this->assertFieldsNotRolledForward($oldvideotime, $newvideotime, ['interval']);
    }
}
