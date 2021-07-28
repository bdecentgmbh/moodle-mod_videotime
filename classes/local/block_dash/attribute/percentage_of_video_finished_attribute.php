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
 * Transforms data to number of unique visitors.
 *
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\local\block_dash\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use mod_videotime\videotime_instance;

/**
 * Transforms data to number of unique visitors.
 *
 * @package mod_videotime
 */
class percentage_of_video_finished_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param int $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        global $DB;

        $instance = videotime_instance::instance_by_id($data);

        $total = $DB->get_field_sql('SELECT COUNT(DISTINCT(vts.user_id))
                                       FROM {videotime_session} vts
                                      WHERE vts.module_id = ?', [$instance->get_cm()->id]);
        $finished = $DB->get_field_sql('SELECT COUNT(*)
                                          FROM {videotime_session} vts
                                         WHERE vts.module_id = ? AND vts.state = 1', [$instance->get_cm()->id]);

        if ($total <= 0) {
            return '0%';
        }

        return (round($finished / $total) * 100) . '%';
    }
}
