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
 * Class to render Chat tab
 *
 * @package     videotimetab_chat
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace videotimetab_chat\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/comment/lib.php');

use cache;
use comment;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use core_course\external\course_summary_exporter;
use videotimetab_chat\socket;

/**
 * Class to render Chat tab
 *
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {
    /**
     * @var $context Module context
     */
    protected $context = null;

    /**
     * @var $socket Deft socket
     */
    protected $socket = null;

    /**
     * Constructor.
     *
     * @param int $context The context of the block.
     */
    public function __construct($context) {
        $this->context = $context;
        $this->socket = new socket($context);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $responses = array_map(function ($option) {
            return ['option' => $option];
        }, array_filter($this->config->option ?? []));

        $comments = new comments($this->context);

        return [
            'canuse' => has_capability('block/deft:manage', $this->context),
            'contextid' => $this->context->id,
            'instanceid' => $this->context->instanceid,
            'uniqid' => uniqid(),
            'comments' => $output->render($comments),
            'throttle' => get_config('block_deft', 'throttle'),
            'token' => $this->socket->get_token(),
        ];
    }
}
