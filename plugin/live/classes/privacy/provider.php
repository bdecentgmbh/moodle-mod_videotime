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
 * @package     videotimeplugin_live
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimeplugin_live\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * The videotimeplugin_live module privacy provider
 *
 * @package     videotimeplugin_live
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider,
    // This plugin stores personal data.
    \core_privacy\local\metadata\provider,
    // This plugin is a core_user_data_provider.
    \core_privacy\local\request\plugin\provider {
    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $items): collection {
        $items->add_database_table(
            'videotimeplugin_live_peer',
            [
                'userid' => 'privacy:metadata:videotimeplugin_live_peer:userid',
                'timecreated' => 'privacy:metadata:videotimeplugin_live_peer:timecreated',
                'timemodified' => 'privacy:metadata:videotimeplugin_live_peer:timemodified',
                'mute' => 'privacy:metadata:videotimeplugin_live_peer:mute',
                'status' => 'privacy:metadata:videotimeplugin_live_peer:status',
            ],
            'privacy:metadata:videotimeplugin_live_peer'
        );

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        // Fetch all peer data.
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {videotime} v ON v.id = cm.instance
            INNER JOIN {videotimeplugin_live_peer} p ON p.videotime = cm.instance
                 WHERE p.userid = :userid
              GROUP BY c.id";

        $params = [
            'modname' => 'videotime',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        // Fetch all users with videotime peer data.
        $sql = "SELECT p.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {videotime} v ON v.id = cm.instance
                  JOIN {videotimeplugin_live_peer} p ON p.videotime = cm.instance
                 WHERE cm.id = :cmid";

        $params = [
            'cmid' => $context->instanceid,
            'modname' => 'videotime',
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        [$contextsql, $contextparams] = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT p.id,
                       cm.id AS cmid,
                       p.timecreated,
                       p.timemodified,
                       p.mute,
                       p.status
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {videotime} v ON v.id = cm.instance
            INNER JOIN {videotimeplugin_live_peer} p ON p.videotime = cm.instance
                 WHERE c.id {$contextsql}
                       AND p.userid = :userid
              ORDER BY cm.id";

        $params = ['modname' => 'videotime', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id] + $contextparams;

        // Reference to the videotime activity seen in the last iteration of the loop. By comparing this with the current
        // record, and because we know the results are ordered, we know when we've moved to the peers to a new videotime.
        // when we can export the complete data for the last activity.
        $lastcmid = null;

        $peers = $DB->get_recordset_sql($sql, $params);
        foreach ($peers as $peer) {
            // If we've moved to a new videotime, then write the last peer's data and reinit the peer data array.
            if ($lastcmid != $peer->cmid) {
                if (!empty($peerdata)) {
                    $context = \context_module::instance($lastcmid);
                    self::export_peer_data_for_user($peerdata, $context, $user);
                }
                $peerdata = [
                    'peers' => [],
                    'cmid' => $peer->cmid,
                ];
            }
            $peerdata['peers'][] = [
                'mute' => $peer->mute,
                'status' => $peer->status,
                'timecreated' => \core_privacy\local\request\transform::datetime($peer->timecreated),
                'timemodified' => \core_privacy\local\request\transform::datetime($peer->timemodified),
            ];
            $lastcmid = $peer->cmid;
        }
        $peers->close();

        // The data for the last activity won't have been written yet, so make sure to write it now!
        if (!empty($peerdata)) {
            $context = \context_module::instance($lastcmid);
            self::export_peer_data_for_user($peerdata, $context, $user);
        }
    }

    /**
     * Export the supplied personal data for a single videotime activity, along with any generic data or area files.
     *
     * @param array $peerdata the personal data to export for the videotime activity.
     * @param \context_module $context the context of the choice.
     * @param \stdClass $user the user record
     */
    protected static function export_peer_data_for_user(array $peerdata, \context_module $context, \stdClass $user) {
        // Fetch the generic module data for the videotime activity.
        $contextdata = helper::get_context_data($context, $user);

        // Merge with videotime data and write it.
        $contextdata = (object)array_merge((array)$contextdata, $peerdata);
        writer::with_context($context)->export_data([get_string('privacy:path', 'videotimeplugin_live')], $contextdata);

        // Write generic module intro files.
        helper::export_context_files($context, $user);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('videotime', $context->instanceid)) {
            $DB->delete_records('videotimeplugin_live_peer', ['videotime' => $cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            if ($cm = get_coursemodule_from_id('videotime', $context->instanceid)) {
                $DB->delete_records('videotimeplugin_live_peer', ['videotime' => $cm->instance, 'userid' => $userid]);
            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('videotime', $context->instanceid);

        if (!$cm) {
            // Only videotime module will be handled.
            return;
        }

        $userids = $userlist->get_userids();
        [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $select = "videotime = :videotimeid AND userid $usersql";
        $params = ['videotimeid' => $cm->instance] + $userparams;
        $DB->delete_records_select('videotimeplugin_live_peer', $select, $params);
    }
}
