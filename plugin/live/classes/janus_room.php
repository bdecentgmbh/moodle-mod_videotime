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
 * Janus room handler
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimeplugin_live;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/locallib.php');

use cache;
use context;
use block_deft\janus;
use block_deft\janus_room as janus_room_base;
use moodle_exception;
use stdClass;

/**
 * Janus room handler
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class janus_room extends janus_room_base {
    /**
     * @var Plugin component using room
     */
    protected string $component = 'videotimeplugin_live';

    /**
     * Constructor
     *
     * @param int $id Instance id
     */
    public function __construct(int $id) {
        global $DB, $USER;

        if (!get_config('block_deft', 'enablebridge')) {
            return;
        }

        $this->session = new janus();
        $this->itemid = $id;

        if (
            !$record = $DB->get_record('block_deft_room', [
            'itemid' => $id,
            'component' => $this->component,
            ])
        ) {
            $records = $DB->get_records('block_deft_room', ['itemid' => null]);
            if ($record = reset($records)) {
                $record->itemid = $this->itemid;
                $record->usermodified = $USER->id;
                $record->timemodified = time();
                $record->component = $this->component;
                $DB->update_record('block_deft_room', $record);
            } else {
                $this->create_room();
            }
        }

        $this->record = $record;

        $this->roomid = $record->roomid ?? 0;
        $this->secret = $record->secret ?? '';
        $this->server = $record->server ?? '';

        $this->init_room();
    }

    /**
     * Check room availabity and create if necessary
     */
    protected function init_room() {
        $exists = [
            'request' => 'exists',
            'room' => $this->roomid,
        ];

        $response = $this->videoroom_send($exists);
        if (!$response->plugindata->data->exists) {
            return $this->create_room();
        }

        $this->set_token();
    }
}
