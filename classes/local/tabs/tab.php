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
 * Tab.
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\local\tabs;

use mod_videotime\videotime_instance;
use moodle_form;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package mod_videotime
 */
abstract class tab {

    /**
     * @var videotime_instance
     */
    private $instance;

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var bool
     */
    private $persistent = false;

    /**
     * Constructor
     *
     * @param videotime_instance $instance
     */
    public function __construct(videotime_instance $instance) {
        $this->instance = $instance;
    }

    /**
     * Get video time instance
     *
     * @return videotime_instance
     */
    public function get_instance(): videotime_instance {
        return $this->instance;
    }

    /**
     * Set active
     *
     */
    public function set_active(): void {
        $this->active = true;
    }

    /**
     * Get active state
     *
     * @return bool
     */
    public function get_active(): bool {
        return $this->active;
    }

    /**
     * Set persistent
     *
     */
    public function set_persistent(): void {
        $this->persistent = true;
    }

    /**
     * Get persistent state
     *
     * @return bool
     */
    public function get_persistent(): bool {
        return $this->persistent;
    }

    /**
     * Get tab name for ids
     *
     * @return string
     */
    abstract public function get_name(): string;

    /**
     * Get label for tab
     *
     * @return string
     */
    abstract public function get_label(): string;

    /**
     * Get tab panel content
     *
     * @return string
     */
    abstract public function get_tab_content(): string;

    /**
     * Get data
     *
     * @return array
     */
    public function get_data(): array {
        return [
            'name' => $this->get_name(),
            'label' => $this->get_label(),
            'active' => $this->get_active(),
            'persistent' => $this->get_persistent(),
            'tabcontent' => $this->get_tab_content()
        ];
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
    }

    /**
     * Saves current settings in database if necessary
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
    }

    /**
     * Delete settings in database
     *
     * @param  int $id
     */
    public static function delete_settings(int $id) {
    }

    /**
     * Prepares the form before data are set
     *
     * @param  array $defaultvalues
     * @param  int $instance
     */
    public static function data_preprocessing(array &$defaultvalues, int $instance) {
    }

    /**
     * Report file areas for backup
     *
     * @return array
     */
    public static function get_config_file_areas(): array {
        return array();
    }
}
