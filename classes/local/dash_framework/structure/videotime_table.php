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
 * Class videotime_table.
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
 * Class videotime_table.
 *
 * @package mod_videotime
 */
class videotime_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('videotime', 'vt');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('modulename', 'videotime');
    }

    /**
     * Get fields
     *
     * @return field_interface[]
     */
    public function get_fields(): array {

        $fields = [
            new field('id', new lang_string('pluginname', 'videotime'), $this, null, [
                new identifier_attribute()
            ]),
            new field('name', new lang_string('activity_name', 'videotime'), $this),
            new field('url', new lang_string('videotimeurl', 'videotime'), $this, 'vt.id', [
                new moodle_url_attribute(['url' => new moodle_url('/mod/videotime/view.php', ['v' => 'vt_id'])])
            ]),
            new field('link', new lang_string('videotimelink', 'videotime'), $this, 'vt.id', [
                new moodle_url_attribute(['url' => new moodle_url('/mod/videotime/view.php', ['v' => 'vt_id'])]),
                new link_attribute(['label' => get_string('view')])
            ]),
            new field('video_description', new lang_string('video_description', 'videotime'), $this, 'vt.id', [
                new notes_attribute()
            ]),
            new field('intro', new lang_string('moduleintro'), $this, 'vt.id', [
                new intro_attribute()
            ]),

        ];

        if (videotime_has_pro()) {
            $fields = array_merge($fields, [
                new field('unique_visitors', new lang_string('totaluniquevisitors', 'videotime'), $this, 'vt.id', [
                    new unique_visitors_attribute()
                ]),
                new field('views', new lang_string('totalviews', 'videotime'), $this, 'vt.id', [
                    new views_attribute()
                ]),
                new field('average_view_time', new lang_string('averageviewtime', 'videotime'), $this, 'vt.id', [
                    new average_view_time_attribute(),
                    new time_attribute()
                ]),
                new field('percentage_of_video_finished', new lang_string(
                    'percentageofvideofinished', 'videotime'), $this, 'vt.id', [
                        new percentage_of_video_finished_attribute()
                    ]
                ),
                new field('firstsession', new lang_string('firstsession', 'videotime'), $this, 'vt.id', [
                    new first_session_attribute(),
                    new date_attribute()
                ]),
                new field('lastsession', new lang_string('lastsession', 'videotime'), $this, 'vt.id', [
                    new last_session_attribute(),
                    new date_attribute()
                ]),
            ]);
        }

        if (videotime_has_repository()) {
            $fields = array_merge($fields, [
                new field('videocreated', new lang_string('videocreated', 'videotime'), $this, 'vt.id', [
                    new video_created_attribute(),
                    new date_attribute()
                ]),
                new field('preview_url', new lang_string('preview_picture_url', 'videotime'), $this, 'vt.id', [
                    new image_url_attribute(),
                    new video_preview_attribute()
                ]),
                new field('preview_image', new lang_string('preview_picture', 'videotime'), $this, 'vt.id', [
                    new video_preview_attribute(),
                    new image_attribute()
                ]),
                new field('preview_image_linked', new lang_string('preview_picture_linked', 'videotime'), $this, 'vt.id', [
                    new video_preview_attribute(),
                    new image_attribute(),
                    new linked_data_attribute(['url' => new moodle_url('/mod/videotime/view.php', ['v' => 'vt_id'])])
                ]),
                new field('completion_on_view_time', new lang_string('completion_on_view', 'videotime'), $this, null, [
                    new bool_attribute()
                ]),
                new field('completion_on_view_time_second', new lang_string('completion_on_view_seconds', 'videotime'), $this),
                new field('completion_on_finish', new lang_string('completion_on_finish', 'videotime'), $this, null, [
                    new bool_attribute()
                ]),
                new field('completion_on_percent', new lang_string('completion_on_percent', 'videotime'), $this, null, [
                    new bool_attribute()
                ]),
                new field('completion_on_percent_value', new lang_string('completion_on_percent_value', 'videotime'), $this, null),
            ]);
        }

        return $fields;
    }
}
