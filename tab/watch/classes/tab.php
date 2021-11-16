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
 * @package     videotimetab_watch
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_watch;

defined('MOODLE_INTERNAL') || die();

use context_module;

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_watch
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab panel content
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $OUTPUT;

        $record = $this->get_instance()->to_record();
        $record->uniqueid = $this->get_instance()->get_uniqueid();

        $instance = $this->get_instance();
        $cm = get_coursemodule_from_instance('videotime', $instance->id, $instance->course);
        $context = context_module::instance($cm->id);
        $record->video_description = file_rewrite_pluginfile_urls($record->video_description, 'pluginfile.php',
            $context->id, 'mod_videotime', 'video_description', 0);
        $record->video_description = format_text($record->video_description, $record->video_description_format);
        return $record->video_description;
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
    }
}
