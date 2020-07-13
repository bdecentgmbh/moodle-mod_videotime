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
 * Video Time instance tests.
 *
 * @package   videotime
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\videotime_instance;

defined('MOODLE_INTERNAL') || die();

/**
 * Class videotime_instance_test
 *
 * @group videotime
 * @group videotime_instance_test
 */
class videotime_instance_test extends advanced_testcase {

    private $course;
    private $instancerecord;

    /**
     * @var videotime_instance
     */
    private $videotimeinstance;

    public function setUp() {
        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course();
        $this->instancerecord = $this->getDataGenerator()->create_module('videotime', [
            'course' => $this->course->id,
            'label_mode' => 0
        ]);
        $this->videotimeinstance = videotime_instance::instance_by_id($this->instancerecord->id);
    }

    public function tearDown()
    {
        $this->course = null;
        $this->instancerecord = null;
        $this->videotimeinstance = null;
    }

    public function test_force_settings() {
        $this->assertNotEmpty($this->videotimeinstance->get_force_settings());
        $this->assertFalse(in_array(1, $this->videotimeinstance->get_force_settings()));
        $this->assertFalse($this->videotimeinstance->is_field_forced('label_mode'));

        set_config('label_mode', 2, 'videotime');
        set_config('label_mode_force', 1, 'videotime');

        $this->videotimeinstance = videotime_instance::instance_by_id($this->instancerecord->id);

        $this->assertTrue($this->videotimeinstance->is_field_forced('label_mode'));
        $this->assertEquals(2, $this->videotimeinstance->get_forced_value('label_mode'));

        $this->assertNotEmpty($this->videotimeinstance->to_record());
        $this->assertEquals(2, $this->videotimeinstance->to_record()->label_mode);
    }
}