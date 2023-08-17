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
 * WebSocket manager
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimeplugin_live;

use context;
use moodle_exception;
use stdClass;

/**
 * Web socket manager
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class socket extends \block_deft\socket {
    /**
     * @var Component
     */
    protected const COMPONENT = 'videotimeplugin_live';

    /**
     * Validate context and availabilty
     */
    public function validate() {
        if (
            $this->context->contextlevel != CONTEXT_MODULE
        ) {
            throw new moodle_exception('invalidcontext');
        }
        if (
            !get_coursemodule_from_id('videotime', $this->context->instanceid)
        ) {
            throw new moodle_exception('invalidcontext');
        }
    }
}
