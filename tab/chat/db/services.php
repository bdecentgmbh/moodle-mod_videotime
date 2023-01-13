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
 * Core external functions and service definitions.
 *
 * @package    videotimetab_chat
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'videotimetab_chat_add_comments' => [
        'classname' => 'core_comment_external',
        'methodname' => 'add_comments',
        'description' => 'Adds a comment or comments.',
        'type' => 'write',
        'ajax' => true,
    ],
    'videotimetab_chat_delete_comments' => [
        'classname' => 'core_comment_external',
        'methodname' => 'delete_comments',
        'description' => 'Deletes a comment or comments.',
        'type' => 'write',
        'ajax' => true,
    ],
];
