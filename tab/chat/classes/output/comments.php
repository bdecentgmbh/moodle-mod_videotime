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
 * Class containing data for chat tab
 *
 * @package     videotimetab_chat
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace videotimetab_chat\output;

use videotimetab_chat\comment;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use core_course\external\course_summary_exporter;

/**
 * Class containing data for chat tab
 *
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comments implements renderable, templatable {
    /**
     * @var $context Module context
     */
    protected $context = null;

    /**
     * @var $comment Comment object
     */
    protected $comment = null;

    /**
     * Constructor.
     *
     * @param int $context The context of the block.
     */
    public function __construct($context) {
        $this->context = $context;
        $course = get_course($context->get_course_context()->instanceid);
        $args = new stdClass();
        $args->context   = $context;
        $args->course    = $course;
        $args->area      = 'chat';
        $args->itemid    = 0;
        $args->component = 'videotimetab_chat';
        $args->notoggle  = true;
        $args->showcount  = true;
        $args->autostart = true;
        $args->displaycancel = false;
        $this->comment = new comment($args);
        $this->comment->set_view_permission(true);
        $this->comment->set_fullwidth();
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {

        return [
            'count' => 4,
            'rawcomments' => !empty($this->comment) ? $this->comment->get_comments() : null,
        ];
    }
}
