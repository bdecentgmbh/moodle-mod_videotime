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
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\link_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use lang_string;
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
            new field('video_description', new lang_string('video_description', 'videotime'), $this), // Notes
            new field('intro', new lang_string('video_description', 'videotime'), $this), // Notes
            new field('video_notes', new lang_string('video_description', 'videotime'), $this)
        ];

        if (videotime_has_pro()) {
            $fields = array_merge($fields, [

            ]);
        }

        return $fields;
    }
}