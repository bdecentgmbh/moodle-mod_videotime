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

namespace videotimetab_interaction\event;

use context_module;
use core\event\base;
use stdClass;

/**
 * The interaction_viewed event class.
 *
 * @package     videotimetab_interaction
 * @category    event
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interaction_viewed extends base {
    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'videotimetab_interaction_cue';
    }

    /**
     * Creates an instance from interaction record
     *
     * @param stdClass $interaction Interaction record
     * @param context_module $context Context
     * @return timer_ended
     */
    public static function create_from_record(stdClass $interaction, context_module $context) {
        $event = self::create([
            'objectid' => $interaction->id,
            'context' => $context,
            'other' => [
                'starttime' => $interaction->starttime ?? 0,
            ],
        ]);
        $event->add_record_snapshot('videotimetab_interaction_cue', $interaction);
        return $event;
    }

    /**
     * Returns localised general event name.
     *
     * Override in subclass, we can not make it static and abstract at the same time.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventinteractionviewed', 'videotimetab_interaction');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/videotime/view.php', [
            'id' => $this->contextinstanceid,
            'time' => $this->other['starttime'] ?? 0,
        ]);
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' viewed interaction with id '$this->objectid'"
            . " in Video Time with course module id '$this->contextinstanceid'.";
    }

    /**
     * Map objectid for backup
     *
     * return array
     */
    public static function get_objectid_mapping() {
        return ['db' => 'videotime', 'restore' => 'videotime'];
    }
}
