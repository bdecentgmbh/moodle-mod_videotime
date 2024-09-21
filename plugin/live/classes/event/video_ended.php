<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace videotimeplugin_live\event;

use core\event\base;

/**
 * The video ended event
 *
 * @package     videotimeplugin_live
 * @category    event
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video_ended extends base {
    /**
     * Set all required data properties:
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'videotime';
    }

    /**
     * Validate their custom data (such as $this->data['other'], contextlevel, etc.).
     *
     * Throw \coding_exception or debugging() notice in case of any problems.
     */
    protected function validate_data() {
        // Override if you want to validate event properties when
        // creating new events.
    }

    /**
     * Returns localised general event name.
     *
     * Override in subclass, we can not make it static and abstract at the same time.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventvideoended', 'block_deft');
    }

    /**
     * Get backup mappinig
     *
     * @return array
     */
    public static function get_objectid_mapping() {
        return ['db' => 'videotime', 'restore' => 'task'];
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/videotime/view.php', ['v' => $this->objectid]);
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' ended live video in videotime with id '$this->objectid'.";
    }
}
