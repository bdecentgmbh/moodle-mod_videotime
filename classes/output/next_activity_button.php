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
 * Next activity button helper class
 *
 * @package     mod_videotime
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\output;

use dml_exception;
use mod_videotime\videotime_instance;
use moodle_exception;
use moodle_url;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Next activity button helper class
 *
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class next_activity_button implements \templatable, \renderable {

    /**
     * @var \cm_info
     */
    private $cm;

    /**
     * @var \cm_info
     */
    private $nextcm;

    /**
     * @var string
     */
    private $availabilityinfo = '';

    /**
     * @var bool
     */
    private $isrestricted = false;

    /**
     * @var stdClass
     */
    private $moduleinstance = null;

    /**
     * Constructor
     *
     * @param \cm_info $cm course module info
     */
    public function __construct(\cm_info $cm) {
        $this->cm = $cm;

        $this->moduleinstance = videotime_instance::instance_by_id($cm->instance);

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = self::get_available_cms($cm->course);

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods <= 1) {
            return;
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($cm->id, $modids);

        // Check if we have a next mod to show.
        if ($this->moduleinstance->next_activity_button) {
            if ($this->moduleinstance->next_activity_id == -1 && $position < ($nummods - 1)) {
                $this->nextcm = $mods[$modids[$position + 1]];
            } else if ($this->moduleinstance->next_activity_id > 0) {
                $this->nextcm = $mods[$this->moduleinstance->next_activity_id];
            }
        }

        if ($this->nextcm) {
            if (!empty($this->nextcm->availableinfo)) {
                $this->availabilityinfo = \core_availability\info::format_info(
                    $this->nextcm->availableinfo,
                    $this->nextcm->course
                );
            }

            $this->isrestricted = !$this->nextcm->uservisible;
        }
    }

    /**
     * Return next course module
     *
     * @return \cm_info|mixed
     */
    public function get_next_cm() {
        return $this->nextcm;
    }

    /**
     * Get next cm URL.
     *
     * @return moodle_url|null
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_next_cm_url() {
        if (!$this->nextcm) {
            return null;
        }

        $url = $this->nextcm->url;
        if ($this->nextcm->modname == 'videotime') {
            if ($instance = videotime_instance::instance_by_id($this->nextcm->instance)) {
                if ($instance->label_mode == videotime_instance::PREVIEW_MODE) {
                    $url = new moodle_url('/mod/videotime/view.php', ['id' => $this->nextcm->id]);
                }
            }
        }

        return $url;
    }

    /**
     * Whether next activity is unavailable
     *
     * @return bool
     */
    public function is_restricted() {
        return $this->isrestricted;
    }

    /**
     * Return data for next course module
     *
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_data() {
        if (!$this->nextcm) {
            return [];
        }

        if ($url = $this->get_next_cm_url()) {
            $url = $url->out(false);
        }

        return [
            'cm' => $this->cm,
            'nextcm_url' => $url,
            'nextcm_name' => $this->nextcm->get_formatted_name(),
            'hasnextcm' => !empty($this->nextcm),
            'availability_info' => $this->availabilityinfo,
            'availability_title' => videotime_is_totara() ? strip_tags($this->availabilityinfo) : null,
            'is_restricted' => $this->isrestricted,
            'instance' => $this->moduleinstance->to_record()
        ];
    }

    /**
     * Export data for template
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        return $this->get_data();
    }

    /**
     * Get activities that can be used as the "next activity".
     *
     * @param int $courseid course id
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_available_cms($courseid) {
        $cms = [];
        $modinfo = get_fast_modinfo($courseid);
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->modname == 'videotime') {
                // Some video time instances should be skipped.
                if ($instance = videotime_instance::instance_by_id($cm->instance)) {
                    if ($instance->label_mode == videotime_instance::LABEL_MODE) {
                        // Skip video time instances in label mode.
                        continue;
                    }
                }
            } else if (!$cm->url) {
                // Skip label-like activities.
                continue;
            }

            // Only add activities that aren't in stealth mode.
            if (videotime_is_totara()) {
                if (!$cm->visibleoncoursepage || ($cm->visible &&
                        ($section = $modinfo->get_section_info($cm->sectionnum)) && !$section->visible)) {
                    continue;
                }
            }

            $cms[$cm->id] = $cm;
        }

        return $cms;
    }
}
