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
 * User steps definition.
 *
 * @package    mod_videotime
 * @category   test
 * @copyright  2023 bdecent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException;

/**
 * Steps definitions for users.
 *
 * @package    mod_videotime
 * @category   test
 * @copyright  2023 bdecent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_videotime extends behat_base {
    /**
     * Convert page names to URLs for steps like 'When I am on the "[page name]" page'.
     *
     * Recognised page names are:
     * | Page name            | Description                                                 |
     *
     * @param string $page name of the page, with the component name removed e.g. 'Admin notification'.
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_url(string $page): moodle_url {

        switch (strtolower($page)) {
            default:
                throw new Exception("Unrecognised Video Time page type '{$page}'.");
        }
    }

    /**
     * Convert page names to URLs for steps like 'When I am on the "[identifier]" "[page type]" page'.
     *
     * Recognised page names are:
     * | pagetype          | name meaning                                | description                                  |
     * | Embed options     | Embed options                               | The Video Time this player options           |
     *
     * @param string $type identifies which type of page this is, e.g. 'Attempt review'.
     * @param string $identifier identifies the particular page, e.g. 'Test quiz > student > Attempt 1'.
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        global $DB;

        switch (strtolower($type)) {
            case 'embed options':
                return new moodle_url(
                    '/mod/videotime/options.php',
                    ['id' => $this->get_cm_by_videotime_name($identifier)->id]
                );

            default:
                throw new Exception("Unrecognised Video Time page type '{$type}'.");
        }
    }

    /**
     * Get a videotime by name.
     *
     * @param string $name videotime name.
     * @return stdClass the corresponding DB row.
     */
    protected function get_videotime_by_name(string $name): stdClass {
        global $DB;
        return $DB->get_record('videotime', ['name' => $name], '*', MUST_EXIST);
    }

    /**
     * Get a videotime cmid from the videotime name.
     *
     * @param string $name videotime name.
     * @return stdClass cm from get_coursemodule_from_instance.
     */
    protected function get_cm_by_videotime_name(string $name): stdClass {
        $videotime = $this->get_videotime_by_name($name);
        return get_coursemodule_from_instance('videotime', $videotime->id, $videotime->course);
    }
}
