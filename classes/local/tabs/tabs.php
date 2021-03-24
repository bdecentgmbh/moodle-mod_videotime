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

namespace mod_videotime\local\tabs;

use mod_videotime\videotime_instance;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Class videotime_table.
 *
 * @package mod_videotime
 */
class tabs implements \templatable, \renderable {

    /**
     * @var tab[]
     */
    private $tabs = [];

    /**
     * @var videotime_instance
     */
    private $instance;

    public function __construct(videotime_instance $instance) {
        $this->instance = $instance;
        $this->tabs[] = new watch_tab($instance);
        $this->tabs[] = new information_tab($instance);

        $this->set_active_tab('watch');
        $this->get_tab('watch')->set_persistent();
    }

    public function set_active_tab(string $tabname): void {
        foreach ($this->tabs as $tab) {
            if ($tab->get_name() == $tabname) {
                $tab->set_active();
                break;
            }
        }
    }

    public function get_tab(string $tabname): ?tab {
        foreach ($this->tabs as $tab) {
            if ($tab->get_name() == $tabname) {
                return $tab;
            }
        }
    }

    public function export_for_template(renderer_base $output) {
        $tabs = [];

        foreach ($this->tabs as $tab) {
            $tabs[] = $tab->get_data();
        }

        return [
            'tabs' => $tabs
        ];
    }
}