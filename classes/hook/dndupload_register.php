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

namespace mod_videotime\hook;

defined('MOODLE_INTERNAL') || die();

use stdClass;

#[\core\attribute\label('Allows plugins add file extensions to support drag and drop upload.')]
#[\core\attribute\tags('mod_videotime')]
/**
 * Hook to allow components to add Video Time drag and drop file extensions
 *
 * @package    mod_videotime
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class dndupload_register {
    /**
     * @var $extensions File extensions
     */
    protected $extensions = null;

    /**
     * Constructor for the hook
     */
    public function __construct() {
        $this->extensions = [];
    }

    /**
     * Get the file extensions
     *
     * @return array File extensions to handle
     */
    public function get_extensions(): array {
        return $this->extensions;
    }

    /**
     * Register an extension to handle
     *
     * @param string $ext Extension
     */
    public function register_handler(string $ext) {
        $this->extensions[] = $ext;
    }
}
