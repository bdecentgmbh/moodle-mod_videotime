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

namespace mod_videotime\plugininfo;

use core\update\info;

/**
 * Plugin version and other meta-data are defined here.
 *
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class videotimeplugin extends \core\plugininfo\base {
    /**
     * If there are updates for this plugin available, returns them.
     *
     * Returns array of \core\update\info objects, if some update
     * is available. Returns null if there is no update available or if the update
     * availability is unknown.
     *
     * Populates the property $availableupdates on first call (lazy loading).
     *
     * @return array|null
     */
    public function available_updates() {

        $updates = parent::available_updates();

        switch ($this->name) {
            case 'pro':
                $info = array(
                    'maturity' => MATURITY_STABLE,
                    'release' => '1.5',
                    'version' => 2022022800,
                );
                break;
            case 'repository':
                $info = array(
                    'maturity' => MATURITY_STABLE,
                    'release' => '1.5',
                    'version' => 2022022800,
                );
                break;
        }
        if (!empty($info) && $this->versiondb < $info['version']) {
            $updates['videotimeplugin_' . $this->name] = new info('videotimeplugin_' . $this->name, $info);
        }

        return $updates;
    }
}
