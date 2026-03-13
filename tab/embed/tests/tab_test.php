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
 * Unit tests for the Embed tab plugin.
 *
 * @package     videotimetab_embed
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_embed;

use advanced_testcase;
use mod_videotime\videotime_instance;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->dirroot/mod/videotime/tab/embed/db/install.php");

/**
 * Tests for videotimetab_embed.
 *
 * @group videotimetab_embed
 * @group mod_videotime
 * @covers \videotimetab_embed\tab
 */
final class tab_test extends advanced_testcase {

    /** @var \stdClass */
    private $course;

    /** @var \stdClass generated videotime module record */
    private $modulerecord;

    /** @var videotime_instance */
    private $instance;

    /** @var \stdClass test user */
    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course([
            'shortname' => 'TEST101',
            'fullname'  => 'Test Course',
            'idnumber'  => 'COURSE-001',
        ]);
        $this->modulerecord = $this->getDataGenerator()->create_module('videotime', [
            'course'    => $this->course->id,
            'vimeo_url' => 'https://vimeo.com/347119375',
        ]);
        $this->instance = videotime_instance::instance_by_id($this->modulerecord->id);

        $this->user = $this->getDataGenerator()->create_user([
            'username'  => 'testuser',
            'firstname' => 'Jane',
            'email'     => 'jane@example.com',
        ]);
        $this->setUser($this->user);
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
            function_exists('xmldb_videotimetab_embed_install'),
            'xmldb_videotimetab_embed_install() must be defined in db/install.php'
        );
        $this->assertTrue(xmldb_videotimetab_embed_install());
    }

    // -------------------------------------------------------------------------
    // is_visible()
    // -------------------------------------------------------------------------

    /**
     * Tab is not visible when no DB record exists (not enabled for the instance).
     */
    public function test_is_visible_false_when_not_enabled(): void {
        set_config('embedurl', 'https://example.com/tool', 'videotimetab_embed');

        $tab = new tab($this->instance);
        $this->assertFalse($tab->is_visible());
    }

    /**
     * Tab is not visible when enabled per-instance but no URL is configured.
     */
    public function test_is_visible_false_when_no_url_configured(): void {
        global $DB;

        set_config('embedurl', '', 'videotimetab_embed');
        $DB->insert_record('videotimetab_embed', ['videotime' => $this->modulerecord->id]);

        $tab = new tab($this->instance);
        $this->assertFalse($tab->is_visible());
    }

    /**
     * Tab is visible when enabled per-instance and a URL template is configured.
     */
    public function test_is_visible_true_when_enabled_and_url_configured(): void {
        global $DB;

        set_config('embedurl', 'https://example.com/tool', 'videotimetab_embed');
        $DB->insert_record('videotimetab_embed', ['videotime' => $this->modulerecord->id]);

        $tab = new tab($this->instance);
        $this->assertTrue($tab->is_visible());
    }

    // -------------------------------------------------------------------------
    // Placeholder substitution
    // -------------------------------------------------------------------------

    /**
     * {username} placeholder is replaced with the current user's username.
     */
    public function test_placeholder_username_substituted(): void {
        set_config('embedurl', 'https://example.com/tool?u={username}', 'videotimetab_embed');

        $tab     = new tab($this->instance);
        $content = $tab->get_tab_content();

        $this->assertStringContainsString('u=testuser', $content);
        $this->assertStringNotContainsString('{username}', $content);
    }

    /**
     * {email} placeholder is replaced with the current user's email address.
     */
    public function test_placeholder_email_substituted(): void {
        set_config('embedurl', 'https://example.com/tool?mail={email}', 'videotimetab_embed');

        $tab     = new tab($this->instance);
        $content = $tab->get_tab_content();

        $this->assertStringContainsString('mail=jane%40example.com', $content);
        $this->assertStringNotContainsString('{email}', $content);
    }

    /**
     * {courseshortname} placeholder is replaced with the course short name.
     */
    public function test_placeholder_course_substituted(): void {
        set_config('embedurl', 'https://example.com/tool?c={courseshortname}', 'videotimetab_embed');

        $tab     = new tab($this->instance);
        $content = $tab->get_tab_content();

        $this->assertStringContainsString('c=TEST101', $content);
        $this->assertStringNotContainsString('{courseshortname}', $content);
    }

    /**
     * Placeholders NOT present in the URL template are not injected into the output.
     */
    public function test_unused_placeholders_not_in_output(): void {
        // Template uses only {username} — {email} literal brace syntax must not appear.
        set_config('embedurl', 'https://example.com/tool?u={username}', 'videotimetab_embed');

        $tab     = new tab($this->instance);
        $content = $tab->get_tab_content();

        $this->assertStringNotContainsString('{email}', $content);
        $this->assertStringNotContainsString('jane%40example.com', $content);
    }

    /**
     * {videoid} placeholder is replaced with the numeric Vimeo video ID.
     */
    public function test_placeholder_videoid_for_vimeo_url(): void {
        // Vimeo plugin must be enabled for mod_videotime_get_vimeo_id_from_link() to work.
        set_config('enabled', 1, 'videotimeplugin_vimeo');
        set_config('embedurl', 'https://example.com/tool?v={videoid}', 'videotimetab_embed');

        $tab     = new tab($this->instance);
        $content = $tab->get_tab_content();

        $this->assertStringContainsString('v=347119375', $content);
    }

    // -------------------------------------------------------------------------
    // Custom tab name
    // -------------------------------------------------------------------------

    /**
     * get_label() returns the custom name when one is stored.
     */
    public function test_get_label_returns_custom_name(): void {
        global $DB;

        $DB->insert_record('videotimetab_embed', [
            'videotime' => $this->modulerecord->id,
            'name'      => 'My Custom Tool',
        ]);

        $tab = new tab($this->instance);
        $this->assertSame('My Custom Tool', $tab->get_label());
    }

    /**
     * get_label() falls back to the default lang string when no custom name is set.
     */
    public function test_get_label_falls_back_to_default(): void {
        global $DB;

        $DB->insert_record('videotimetab_embed', [
            'videotime' => $this->modulerecord->id,
            'name'      => '',
        ]);

        $tab = new tab($this->instance);
        $this->assertSame(get_string('label', 'videotimetab_embed'), $tab->get_label());
    }

    /**
     * Placeholder values containing special characters are rawurlencode()-encoded.
     */
    public function test_placeholder_values_are_url_encoded(): void {
        global $DB;

        // Give the user a name with a space and an apostrophe.
        $DB->set_field('user', 'firstname', "O'Brien Smith", ['id' => $this->user->id]);
        $this->user->firstname = "O'Brien Smith";

        set_config('embedurl', 'https://example.com/tool?name={firstname}', 'videotimetab_embed');

        $tab     = new tab($this->instance);
        $content = $tab->get_tab_content();

        // rawurlencode turns space → %20, apostrophe → %27.
        $this->assertStringContainsString('name=O%27Brien%20Smith', $content);
    }
}
