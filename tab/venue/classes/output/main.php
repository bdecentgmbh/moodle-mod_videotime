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
 * Class to render Venue tab
 *
 * @package     videotimetab_venue
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace videotimetab_venue\output;

defined('MOODLE_INTERNAL') || die();

use cache;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use videotimeplugin_live\socket;
use user_picture;

/**
 * Class to render Venue tab
 *
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /**
     * Constructor.
     *
     * @param int $context The context of the block.
     */
    public function __construct($instance) {
        global $DB;

        $this->context = $instance->get_context();
        $this->socket = new socket($this->context);
        $this->instance = $instance;

        $this->peerid = $DB->get_field('sessions', 'id', ['sid' => session_id()]);
        $this->settings = $DB->get_record('videotimetab_venue_peer', [
            'videotime' => $instance->id,
            'sessionid' => $this->peerid,
            'status' => 0,
        ]);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE, $USER;

        $user = clone ($USER);
        $user->fullname = fullname($user);
        $userpicture = new user_picture($user);
        $user->pictureurl = $userpicture->get_url($PAGE, $output);
        $user->avatar = $output->user_picture($user, [
            'class' => 'card-img-top p-1 m-1',
            'link' => false,
            'size' => 36,
        ]);
        return [
            'autogaincontrol' => !empty(get_config('block_deft', 'autogaincontrol')),
            'canuse' => has_capability('block/deft:manage', $this->context),
            'contextid' => $this->context->id,
            'echocancellation' => !empty(get_config('block_deft', 'echocancellation')),
            'iceservers' => json_encode($this->socket->ice_servers()),
            'instanceid' => $this->instance->id,
            'mute' => !empty($this->settings->mute),
            'noisesuppression' => !empty(get_config('block_deft', 'noisesuppression')),
            'peerid' => $this->peerid,
            'samplerate' => get_config('block_deft', 'samplerate'),
            'throttle' => get_config('block_deft', 'throttle'),
            'token' => $this->socket->get_token(),
            'roomid' => 0,
            'uniqid' => uniqid(),
            'user' => $user,
        ];
    }
}
