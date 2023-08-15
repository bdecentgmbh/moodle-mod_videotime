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
 * Video Time Vimeo force settings test
 *
 * @package   videotimeplugin_vimeo
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimeplugin_vimeo;

use advanced_testcase;
use mod_videotime\videotime_instance;

/**
 * Class fore_settings_test
 *
 * @group videotimeplugin_vimeo
 * @covers \mod_videotime\videotime_instance
 */
class force_settings_test extends advanced_testcase {

    /**
     * @var stdClass $course
     */
    private $course;
    /**
     * @var stdClass $instancerecord
     */
    private $instancerecord;

    /**
     * @var videotime_instance
     */
    private $videotimeinstance;

    /**
     * Set up
     */
    public function setUp() : void {
        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course();
        $this->instancerecord = $this->getDataGenerator()->create_module('videotime', [
            'course' => $this->course->id,
            'controls' => 0,
            'vimeo_url' => 'https://vimeo.com/347119375',
        ]);
        $this->videotimeinstance = videotime_instance::instance_by_id($this->instancerecord->id);
    }

    /**
     * Tear down data
     */
    public function tearDown() : void {
        $this->course = null;
        $this->instancerecord = null;
        $this->videotimeinstance = null;
    }

    /**
     * Force setting test
     */
    public function test_disable_options() {
        $this->assertIsArray($this->videotimeinstance->get_force_settings());
        $this->assertFalse(in_array(1, $this->videotimeinstance->get_force_settings()));

        set_config('enabled', 1, 'videotimeplugin_vimeo');
        foreach ($this->get_options() as $option) {
            set_config($option, 0, 'videotimeplugin_vimeo');
            set_config('forced', $option, 'videotimeplugin_vimeo');

            $this->videotimeinstance = videotime_instance::instance_by_id($this->instancerecord->id);

            $this->assertNotEmpty($this->videotimeinstance->to_record());
            $this->assertEquals(0, $this->videotimeinstance->to_record()->$option);
        }
    }

    /**
     * Force setting test
     */
    public function test_enable_options() {
        $this->assertIsArray($this->videotimeinstance->get_force_settings());
        $this->assertFalse(in_array(1, $this->videotimeinstance->get_force_settings()));

        set_config('enabled', 1, 'videotimeplugin_vimeo');
        foreach ($this->get_options() as $option) {
            set_config($option, 1, 'videotimeplugin_vimeo');
            set_config('forced', $option, 'videotimeplugin_vimeo');

            $this->videotimeinstance = videotime_instance::instance_by_id($this->instancerecord->id);

            $this->assertNotEmpty($this->videotimeinstance->to_record());
            $this->assertEquals(1, $this->videotimeinstance->to_record()->$option);
        }
    }

    /**
     * Get supported options
     *
     * @return array
     */
    public function get_options() {
        return [
            'autoplay',
            'byline',
            'controls',
            'muted',
            'option_loop',
            'playsinline',
            'portrait',
            'responsive',
            'title',
            'transparent',
        ];
    }
}
