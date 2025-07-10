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

use context_module;
use stdClass;

#[\core\attribute\label('Allows plugins to create Video Time activity from drag and drop uploaded file.')]
#[\core\attribute\tags('mod_videotime')]
/**
 * Hook to allow components to create Video Time activity from drag and drop uploaded file
 *
 * @package    mod_videotime
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class dndupload_handle {
    /** @var $instanced Instance id for module */
    protected ?int $instanceid = null;

    /** @var context_module $context Module context of meeting */
    public ?context_module $context = null;

    /**
     * Constructor for the hook
     *
     * @param stdClass $uploadinfo upload data
     */
    public function __construct(
        /** @var stdClass $uploadinfo upload data */
        protected readonly stdClass $uploadinfo
    ) {
        $this->context = context_module::instance($this->uploadinfo->coursemodule);
    }

    /**
     * Get the context
     *
     * @return context_module The meeting module context
     */
    public function get_context(): context_module {
        return $this->context;
    }

    /**
     * Get the course
     *
     * @return stdClass
     */
    public function get_course(): stdClass {
        return $this->uploadinfo->course;
    }

    /**
     * Get the course module id
     *
     * @return int
     */
    public function get_coursemodule(): int {
        return $this->uploadinfo->coursemodule;
    }

    /**
     * Get the display name
     *
     * @return string
     */
    public function get_displayname(): string {
        return $this->uploadinfo->displayname;
    }

    /**
     * Get the draft item id
     *
     * @return ?int
     */
    public function get_draftitemid(): ?int {
        return $this->uploadinfo->draftitemid;
    }

    /**
     * Get the instance id
     *
     * @return ?int
     */
    public function get_instanceid(): ?int {
        return $this->instanceid;
    }

    /**
     * Get the instance id
     *
     * @param ?int $instanceid Instance id
     */
    public function set_instanceid(?int $instanceid) {
        $this->instanceid = $instanceid;
    }
}
