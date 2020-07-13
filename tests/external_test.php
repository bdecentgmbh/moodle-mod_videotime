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
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once("$CFG->dirroot/webservice/tests/helpers.php");

/**
 * Class external_test
 *
 * @group videotime
 * @group mod_videotime_external_test
 */
class videotime_external_test extends externallib_advanced_testcase {

    private $course;
    private $videotimeinstance;
    private $student;

    public function setUp()
    {
        $this->course = $this->getDataGenerator()->create_course();
        $this->videotimeinstance = $this->getDataGenerator()->create_module('videotime', [
            'course' => $this->course->id,
            'autoplay' => 1,
            'responsive' => 1
        ]);
        $this->student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id);

        parent::setUp();
    }

    public function tearDown()
    {
        $this->course = null;
        $this->videotimeinstance = null;
        $this->student = null;
    }

    public function test_get_embed_options() {
        $this->resetAfterTest();

        $this->setUser($this->student);

        $embedoptions = json_decode(external::get_embed_options($this->videotimeinstance->cmid)['options'], true);
        $this->assertEquals(1, $embedoptions['autoplay']);
        $this->assertEquals(1, $embedoptions['responsive']);
    }

    public function test_view_videotime() {
        $this->resetAfterTest();

        $this->setUser($this->student);
        $this->assertNull(external::view_videotime($this->videotimeinstance->cmid));
    }
}