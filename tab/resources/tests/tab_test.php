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
 * Unit tests for the Resources tab plugin.
 *
 * @package     videotimetab_resources
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_resources;

use advanced_testcase;
use context_module;
use mod_videotime\videotime_instance;
use stored_file;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->dirroot/mod/videotime/tab/resources/db/install.php");

/**
 * Tests for videotimetab_resources.
 *
 * @group videotimetab_resources
 * @group mod_videotime
 * @covers \videotimetab_resources\tab
 */
final class tab_test extends advanced_testcase {

    /** @var \stdClass */
    private $course;

    /** @var \stdClass generated videotime module record */
    private $modulerecord;

    /** @var videotime_instance */
    private $instance;

    /** @var context_module */
    private $context;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course();
        $this->modulerecord = $this->getDataGenerator()->create_module('videotime', [
            'course'    => $this->course->id,
            'vimeo_url' => 'https://vimeo.com/347119375',
        ]);
        $this->instance = videotime_instance::instance_by_id($this->modulerecord->id);

        $cm = get_coursemodule_from_instance('videotime', $this->modulerecord->id, $this->course->id);
        $this->context = context_module::instance($cm->id);
    }

    // -------------------------------------------------------------------------
    // Install function
    // -------------------------------------------------------------------------

    /**
     * The install function must exist and return true.
     *
     * This test would have caught the missing-install-function upgrade error
     * (same class of bug as in videotimetab_liveinteraction).
     */
    public function test_install_function_exists_and_returns_true(): void {
        $this->assertTrue(
            function_exists('xmldb_videotimetab_resources_install'),
            'xmldb_videotimetab_resources_install() must be defined in db/install.php'
        );
        $this->assertTrue(xmldb_videotimetab_resources_install());
    }

    // -------------------------------------------------------------------------
    // is_visible()
    // -------------------------------------------------------------------------

    /**
     * Tab is not visible when no DB record exists (not enabled for the instance).
     */
    public function test_is_visible_false_when_not_enabled(): void {
        $tab = new tab($this->instance);
        $this->assertFalse($tab->is_visible());
    }

    /**
     * Tab is not visible when enabled per-instance but no files have been uploaded.
     */
    public function test_is_visible_false_when_no_files(): void {
        global $DB;

        $DB->insert_record('videotimetab_resources', [
            'videotime' => $this->modulerecord->id,
            'name'      => '',
        ]);

        $tab = new tab($this->instance);
        $this->assertFalse($tab->is_visible());
    }

    /**
     * Tab is visible when enabled and at least one file has been uploaded.
     */
    public function test_is_visible_true_when_enabled_and_files_exist(): void {
        global $DB;

        $DB->insert_record('videotimetab_resources', [
            'videotime' => $this->modulerecord->id,
            'name'      => '',
        ]);

        $this->create_test_file('document.pdf');

        $tab = new tab($this->instance);
        $this->assertTrue($tab->is_visible());
    }

    // -------------------------------------------------------------------------
    // get_label()
    // -------------------------------------------------------------------------

    /**
     * get_label() returns the custom name when one is stored.
     */
    public function test_get_label_returns_custom_name(): void {
        global $DB;

        $DB->insert_record('videotimetab_resources', [
            'videotime' => $this->modulerecord->id,
            'name'      => 'Course Materials',
        ]);

        $tab = new tab($this->instance);
        $this->assertSame('Course Materials', $tab->get_label());
    }

    /**
     * get_label() falls back to the default lang string when no custom name is set.
     */
    public function test_get_label_falls_back_to_default(): void {
        global $DB;

        $DB->insert_record('videotimetab_resources', [
            'videotime' => $this->modulerecord->id,
            'name'      => '',
        ]);

        $tab = new tab($this->instance);
        $this->assertSame(get_string('label', 'videotimetab_resources'), $tab->get_label());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Store a dummy file in the resources file area for the test module instance.
     *
     * @param string $filename
     * @return stored_file
     */
    private function create_test_file(string $filename): stored_file {
        $fs = get_file_storage();
        return $fs->create_file_from_string(
            [
                'contextid' => $this->context->id,
                'component' => 'videotimetab_resources',
                'filearea'  => 'files',
                'itemid'    => 0,
                'filepath'  => '/',
                'filename'  => $filename,
            ],
            'dummy file content'
        );
    }
}
