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
use mod_videotime\plugin_manager;

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

    /**
     * Constructory
     *
     * @param videotime_instance $instance
     */
    public function __construct(videotime_instance $instance) {
        $this->instance = $instance;
        $pluginmanager = new plugin_manager('videotimetab');
        foreach ($pluginmanager->get_sorted_plugins_list() as $subplugin) {
            $classname = "\\videotimetab_$subplugin\\tab";
            $tab = new $classname($instance);
            if ($tab->is_visible()) {
                $this->tabs[] = $tab;
            }
        }

        if (!empty(get_config("videotimetab_watch", 'enabled'))) {
            $this->set_active_tab('watch');
            $this->get_tab('watch')->set_persistent();
        }
    }

    /**
     * Get active tab
     *
     * @param string $tabname
     */
    public function set_active_tab(string $tabname): void {
        foreach ($this->tabs as $tab) {
            if ($tab->get_name() == $tabname) {
                $tab->set_active();
                break;
            }
        }
    }

    /**
     * Get active tab
     *
     * @param string $tabname
     * @return null|tab
     */
    public function get_tab(string $tabname): ?tab {
        foreach ($this->tabs as $tab) {
            if ($tab->get_name() == $tabname) {
                return $tab;
            }
        }
        return null;
    }

    /**
     * Export template data
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $record = $this->get_instance()->to_record();
        $record->uniqueid = $this->get_instance()->get_uniqueid();
        $tabs = [];

        foreach ($this->tabs as $tab) {
            $tabs[] = $tab->get_data();
        }

        $record->intro = '';
        return [
            'id' => $record->id,
            'instance' => [$record],
            'panelclass' => get_config('videotime', 'defaulttabsize'),
            'tabs' => $tabs,
        ];
    }

    /**
     * Get video time instance
     *
     * @return videotime_instance
     */
    public function get_instance(): videotime_instance {
        return $this->instance;
    }
}
