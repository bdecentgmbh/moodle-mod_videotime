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
 * Library functions for Deft.
 *
 * @package   videotimetab_chat
 * @copyright 2022 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/comment/lib.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');

use videotimetab_chat\output\main;
use videotimetab_chat\socket;

/**
 * Validate comment parameter before perform other comments actions
 *
 * @package  videotimetab_chat
 * @category comment
 *
 * @param stdClass $commentparam {
 *              context  => context the context object
 *              courseid => int course id
 *              cm       => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return boolean
 */
function videotimetab_chat_comment_validate($commentparam) {
    if ($commentparam->commentarea != 'chat') {
        throw new comment_exception('invalidcommentarea');
    }
    return true;
}

/**
 * Running addtional permission check on plugins
 *
 * @package  videotimetab_chat
 * @category comment
 *
 * @param stdClass $args
 * @return array
 */
function videotimetab_chat_comment_permissions($args) {
    return [
        'post' => true,
        'view' => true,
    ];
}

/**
 * Validate comment data before displaying comments
 *
 * @package  videotimetab_chat
 * @category comment
 *
 * @param stdClass $comments
 * @param stdClass $args
 * @return boolean
 */
function videotimetab_chat_comment_display($comments, $args) {
    if ($args->commentarea != 'chat') {
        throw new comment_exception('invalidcommentarea');
    }
    return $comments;
}

/**
 * Serve the comments as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function videotimetab_chat_output_fragment_content($args) {
    global $CFG, $OUTPUT;

    $context = $args['context'];

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    $main = new \videotimetab_chat\output\main($context);
    $data = $main->export_for_template($OUTPUT);

    return '<div>' . $data['comments'] . '</div>';
}
