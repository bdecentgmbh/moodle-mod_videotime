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
 * Represents a single Video Time activity module. Adds more functionality when working with instances.
 *
 * @package     videotimeplugin_videojs
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimeplugin_videojs;

use core_component;
use mod_videotime\vimeo_embed;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->dirroot/mod/videotime/lib.php");
require_once("$CFG->libdir/resourcelib.php");

/**
 * Represents a single Video Time activity module. Adds more functionality when working with instances.
 *
 * @package videotimeplugin_videojs
 */
class video_embed extends vimeo_embed implements \renderable, \templatable {

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return \stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $mimetype = resourcelib_guess_url_mimetype($this->record->vimeo_url);

        $context = parent::export_for_template($output) + [
            'mimetype' => $mimetype,
            'video' => !file_mimetype_in_typegroup($mimetype, ['web_audio']),
        ];

        return $context;
    }

    /**
     * Returns the moodle component name.
     *
     * It might be the plugin name (whole frankenstyle name) or the core subsystem name.
     *
     * @return string
     */
    public function get_component_name() {
        return 'videotimeplugin_videojs';
    }
}
