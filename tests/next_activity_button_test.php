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
 * Video Time next instance button tests.
 *
 * @package   mod_videotime
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime;

use cm_info;
use advanced_testcase;
use moodle_url;
use mod_videotime\videotime_instance;

/**
 * Class next_activity_button_test
 *
 * @group videotime
 * @group next_activity_button_test
 */
class next_activity_button_test extends advanced_testcase {

    public function test_get_available_cms() {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course([]);
        $videotime1 = $this->getDataGenerator()->get_plugin_generator('mod_videotime')->create_instance([
            'course' => $course->id,
            'label_mode' => videotime_instance::PREVIEW_MODE
        ]);
        $videotime2 = $this->getDataGenerator()->get_plugin_generator('mod_videotime')->create_instance([
            'course' => $course->id,
            'label_mode' => videotime_instance::LABEL_MODE
        ]);
        $videotime3 = $this->getDataGenerator()->get_plugin_generator('mod_videotime')->create_instance([
            'course' => $course->id,
            'label_mode' => videotime_instance::NORMAL_MODE,
            'next_activity_button' => true,
            'next_activity_id' => $videotime1->cmid
        ]);

        $label = $this->getDataGenerator()->create_module('label', ['course' => $course->id]);
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);

        $availablecms = \mod_videotime\output\next_activity_button::get_available_cms($course->id);

        $this->assertEquals([
            $videotime1->cmid,
            $videotime3->cmid,
            $forum->cmid
        ], array_keys($availablecms));

        $nextactivitybutton = new \mod_videotime\output\next_activity_button(
            cm_info::create(get_coursemodule_from_id(null, $videotime1->cmid))
        );
        $this->assertNull($nextactivitybutton->get_next_cm_url());

        $nextactivitybutton = new \mod_videotime\output\next_activity_button(
            cm_info::create(get_coursemodule_from_id(null, $videotime3->cmid))
        );
        $this->assertEquals((new moodle_url('/mod/videotime/view.php', ['id' => $videotime1->cmid]))->out(false),
            $nextactivitybutton->get_next_cm_url()->out(false));
    }
}
