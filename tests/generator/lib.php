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
 * Plugin version and other meta-data are defined here.
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * VideoTime module data generator class
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videotime_generator extends testing_module_generator {

    /**
     * Create instance
     *
     * @param stdClass $record
     * @param array $options
     */
    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/resourcelib.php');

        $record = (array)$record + [
            'name' => 'Testing video time instance',
            'vimeo_url' => 'https://vimeo.com/228296978',
            'video_description' => '',
            'video_description_format' => FORMAT_HTML,
            'completion_on_view_time' => 0,
            'completion_on_view_time_second' => 0,
            'completion_on_finish' => 0,
            'completion_on_percent' => 0,
            'completion_on_percent_value' => 0,
            'autoplay' => 0,
            'byline' => 0,
            'color' => 0,
            'height' => 0,
            'maxheight' => 0,
            'maxwidth' => 0,
            'muted' => 0,
            'playsinline' => 0,
            'portrait' => 0,
            'speed' => 0,
            'title' => 0,
            'transparent' => 0,
            'autopause' => 0,
            'background' => 0,
            'controls' => 0,
            'pip' => 0,
            'dnt' => 0,
            'width' => 0,
            'responsive' => 0,
            'label_mode' => 0,
            'viewpercentgrade' => 0,
            'next_activity_button' => 0,
            'next_activity_id' => 0,
            'next_activity_auto' => 0,
            'resume_playback' => 0,
            'preview_picture' => 0,
            'show_description' => 0,
            'show_title' => 0,
            'show_tags' => 0,
            'show_duration' => 0,
            'show_viewed_duration' => 0,
            'columns' => 0,
        ];

        return parent::create_instance($record, (array)$options);
    }
}
