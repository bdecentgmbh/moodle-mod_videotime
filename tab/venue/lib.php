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
 * @package   videotimetab_venue
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/locallib.php');

use videotimetab_venue\output\main;
use videotimetab_venue\socket;

/**
 * Serve the comments as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function videotimetab_venue_output_fragment_content($args) {
    global $CFG, $OUTPUT;

    $context = $args['context'];

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    $main = new \videotimetab_venue\output\main($context);
    $data = $main->export_for_template($OUTPUT);

    return '<div>' . $data['comments'] . '</div>';
}

/**
 * Provide venue user information
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function videotimetab_venue_output_fragment_venue($args) {
    global $DB, $OUTPUT, $USER, $PAGE;


    $context = $args['context'];
    $peerid = $args['peerid'];
    $userid = $DB->get_field('sessions', 'userid', [
        'id' => $peerid,
    ]);

    if (!$user = core_user::get_user($userid)) {
        return '';
    }
    $url = new moodle_url('/user/view.php', [
        'id' => $user->id,
        'course' => $context->get_course_context->instance,
    ]);
    $user->fullname = fullname($user);
    $userpicture = new user_picture($user);
    $user->pictureurl = $userpicture->get_url($PAGE, $OUTPUT);
    $user->avatar = $OUTPUT->user_picture($user, [
        'class' => 'card-img-top',
        'link' => false,
        'size' => 32,
    ]);
    $user->manage = has_capability('block/deft:moderate', $context);
    $user->profileurl = $url->out(false);

    return $OUTPUT->render_from_template('block_deft/venue_user', [
        'peerid' => $peerid,
        'user' => $user,
    ]);
}
