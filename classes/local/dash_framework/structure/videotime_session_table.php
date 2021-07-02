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
 * Class videotime_session_table.
 *
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\bool_attribute;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\link_attribute;
use block_dash\local\data_grid\field\attribute\linked_data_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use block_dash\local\data_grid\field\attribute\percent_attribute;
use block_dash\local\data_grid\field\attribute\time_attribute;
use lang_string;
use mod_videotime\local\block_dash\attribute\average_view_time_attribute;
use mod_videotime\local\block_dash\attribute\first_session_attribute;
use mod_videotime\local\block_dash\attribute\intro_attribute;
use mod_videotime\local\block_dash\attribute\last_session_attribute;
use mod_videotime\local\block_dash\attribute\notes_attribute;
use mod_videotime\local\block_dash\attribute\percentage_of_video_finished_attribute;
use mod_videotime\local\block_dash\attribute\unique_visitors_attribute;
use mod_videotime\local\block_dash\attribute\video_created_attribute;
use mod_videotime\local\block_dash\attribute\video_preview_attribute;
use mod_videotime\local\block_dash\attribute\views_attribute;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Class videotime_session_table.
 *
 * @package mod_videotime
 */
class videotime_session_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('videotime_session', 'vts');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('datasource:videotime_sessions_data_source', 'videotime');
    }

    /**
     * Get fields
     *
     * @return field_interface[]
     */
    public function get_fields(): array {
        return [
            new field('id', new lang_string('pluginname', 'videotime'), $this, null, [
                new identifier_attribute()
            ]),
            new field('time', new lang_string('watch_time', 'videotime'), $this, 'SUM(time)', [
                new time_attribute()
            ]),
            new field('state', new lang_string('state_finished', 'videotime'), $this, 'MAX(state)', [
                new bool_attribute()
            ]),
            new field('timestarted', new lang_string('timestarted', 'videotime'), $this, 'MIN(vts.timestarted)', [
                new date_attribute()
            ]),
            new field('percent_watch', new lang_string('watch_percent', 'videotime'), $this, 'MAX(percent_watch)', [
                new percent_attribute()
            ]),
            new field('current_watch_time', new lang_string('currentwatchtime', 'videotime'), $this, 'MAX(current_watch_time)', [
                new time_attribute()
            ])
        ];
    }
}
