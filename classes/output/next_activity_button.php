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
 * @package     mod_videotime
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\output;

use renderer_base;

require_once("$CFG->dirroot/mod/videotime/lib.php");

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
    private $availability_info = '';

    /**
     * @var bool
     */
    private $is_restricted = false;

    public function __construct(\cm_info $cm)
    {
        global $DB;

        $this->cm = $cm;

        $moduleinstance = $DB->get_record('videotime', ['id' => $cm->instance], '*', MUST_EXIST);
        $moduleinstance = videotime_populate_with_defaults($moduleinstance);

        // Get a list of all the activities in the course.
        $modinfo = get_fast_modinfo($this->cm->course);
        $modules = $modinfo->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];

        foreach ($modules as $module) {
            // Only add activities that have a url (eg. mod_label does not).
            if (empty($module->url)) {
                continue;
            }

            // Only add activities that aren't in stealth mode.
            if (videotime_is_totara()) {
                if (!$module->visibleoncoursepage || ($module->visible &&
                        ($section = $modinfo->get_section_info($module->sectionnum)) && !$section->visible)) {
                    continue;
                }
            } else {
                if ($module->is_stealth()) {
                    continue;
                }
            }
            $mods[$module->id] = $module;
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods <= 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($cm->id, $modids);

        // Check if we have a next mod to show.
        if ($moduleinstance->next_activity_button) {
            if ($moduleinstance->next_activity_id == -1 && $position < ($nummods - 1)) {
                $this->nextcm = $mods[$modids[$position + 1]];
            } else if ($moduleinstance->next_activity_id > 0) {
                $this->nextcm = $mods[$moduleinstance->next_activity_id];
            }
        }

        if ($this->nextcm) {
            if (!empty($this->nextcm->availableinfo)) {
                $this->availability_info = \core_availability\info::format_info($this->nextcm->availableinfo, $this->nextcm->course);
            }

            $this->is_restricted = !$this->nextcm->uservisible;
        }
    }

    /**
     * @return \cm_info|mixed
     */
    public function get_next_cm()
    {
        return $this->nextcm;
    }

    /**
     * @return bool
     */
    public function is_restricted()
    {
        return $this->is_restricted;
    }

    /**
     * @return array
     */
    public function get_data()
    {
        if (!$this->nextcm) {
            return [];
        }

        return [
            'cm' => $this->cm,
            'nextcm_url' => $this->nextcm->url->out(false),
            'nextcm_name' => $this->nextcm->name,
            'hasnextcm' => !empty($this->nextcm),
            'availability_info' => $this->availability_info,
            'availability_title' => videotime_is_totara() ? strip_tags($this->availability_info) : null,
            'is_restricted' => $this->is_restricted,
        ];
    }

    public function export_for_template(renderer_base $output)
    {
        return $this->get_data();
    }
}
