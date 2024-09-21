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
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime;

use core_component;
use mod_videotime\local\tabs\tabs;
use mod_videotime\output\next_activity_button;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Represents a single Video Time activity module. Adds more functionality when working with instances.
 *
 * @package mod_videotime
 */
class vimeo_embed implements \renderable, \templatable {
    /**
     * @var $cm Course module
     */
    protected $cm = null;

    /**
     * @var $record Instance rocord
     */
    protected $record = null;

    /**
     * @var $uniqueid Unique id for element
     */
    protected $uniqueid = null;

    /**
     * Constructor
     *
     * @param \stdClass $instancerecord
     */
    public function __construct(\stdClass $instancerecord) {
        $this->uniqueid = uniqid();

        $this->record = (object) $instancerecord;
    }

    /**
     * Get course module of this instance.
     *
     * @return \stdClass
     * @throws \coding_exception
     */
    public function get_cm() {
        if (is_null($this->cm)) {
            $this->cm = get_coursemodule_from_instance('videotime', $this->record->id);
        }
        return $this->cm;
    }

    /**
     * Get unique element ID.
     *
     * @return string
     */
    public function get_uniqueid(): string {
        return $this->uniqueid;
    }

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

        $cm = get_coursemodule_from_instance('videotime', $this->record->id);

        $context = [
            'instance' => json_encode($this->record),
            'cmid' => $cm->id,
            'haspro' => videotime_has_pro(),
            'interval' => $this->record->saveinterval ?? 5,
            'plugins' => file_exists($CFG->dirroot . '/mod/videotime/plugin/pro/templates/plugins.mustache'),
            'uniqueid' => $this->get_uniqueid(),
            'toast' => file_exists($CFG->dirroot . '/lib/amd/src/toast.js'),
            'video_description' => $this->record->video_description,
        ];

        return $context;
    }
}
