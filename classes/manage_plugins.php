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
 * This file contains the classes for the admin settings of the videotime module.
 *
 * @package   mod_videotime
 * @copyright 2021 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime;

defined('MOODLE_INTERNAL') || die();

use core_component;
use core_text;
use moodle_url;
use stdClass;

/**
 * Admin external page that displays a list of the installed submission plugins.
 *
 * @package   mod_videotime
 * @copyright 2021 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manange_plugins extends \admin_externalpage {

    /** @var string the name of plugin subtype */
    private $subtype = '';

    /**
     * The constructor - calls parent constructor
     *
     * @param string $subtype
     */
    public function __construct($subtype) {
        $this->subtype = $subtype;
        $url = new moodle_url('/mod/videotime/adminmanageplugins.php', array('subtype' => $subtype));
        parent::__construct('manage' . $subtype . 'plugins',
                            get_string('manage' . $subtype . 'plugins', 'videotime'),
                            $url);
    }

    /**
     * Search plugins for the specified string
     *
     * @param string $query The string to search for
     * @return array
     */
    public function search($query) {
        if ($result = parent::search($query)) {
            return $result;
        }

        $found = false;

        foreach (core_component::get_plugin_list($this->subtype) as $name => $notused) {
            if (strpos(core_text::strtolower(get_string('pluginname', $this->subtype . '_' . $name)),
                    $query) !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $result = new stdClass();
            $result->page     = $this;
            $result->settings = array();
            return array($this->name => $result);
        } else {
            return array();
        }
    }
}
