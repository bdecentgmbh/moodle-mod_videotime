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

declare(strict_types=1);

namespace mod_videotime\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for the videotime activity.
 *
 * Contains the class for defining mod_videotime's custom completion rules
 * and fetching a videotime instance's completion statuses for a user.
 *
 * @package mod_videotime
 * @copyright bdecent gmbh 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {
    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        // Get videotime details.

        $sessions = \videotimeplugin_pro\module_sessions::get($this->cm->id, $this->userid);
        $rules = $this->cm->customdata['customcompletionrules'];

        switch ($rule) {
            case 'completion_on_view_time':
                $status = $sessions->get_total_time() >= $rules['completion_on_view_time'] ?? 0;
                break;
            case 'completion_on_finish':
                $status = $sessions->is_finished();
                break;
            case 'completion_on_percent':
                $status = !empty($rules['completion_on_percent']) &&
                    (($sessions->get_percent() * 100) >= ($rules['completion_on_percent'] ?? 0));
                break;
            case 'completion_hide_detail':
                // Check whether any enabled condition is incomplete.
                $status = empty($rules['completion_on_view_time']) ||
                    $sessions->get_total_time() >= $rules['completion_on_view_time'] ?? 0;

                $status = $status && (
                    empty($rules['completion_on_finish']) ||
                    $sessions->is_finished()
                );

                $status = $status && (
                    empty($rules['completion_on_percent']) ||
                    (($sessions->get_percent() * 100) >= $rules['completion_on_percent_value'])
                );
                break;
            default:
                $status = false;
                break;
        }

        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        // Completion settings are pro features.
        if (!videotime_has_pro()) {
            return [];
        }

        return [
            'completion_on_view_time',
            'completion_on_finish',
            'completion_on_percent',
            'completion_hide_detail',
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $timespent = format_time($this->cm->customdata['customcompletionrules']['completion_on_view_time'] ?? 0);
        $percentspent = $this->cm->customdata['customcompletionrules']['completion_on_percent'] ?? 0;

        // Only return general description if we are hiding the details.
        if (!empty($this->cm->customdata['customcompletionrules']['completion_hide_detail'])) {
            return [
                'completion_hide_detail' => get_string('completeactivity', 'core_completion'),
            ];
        }

        return [
            'completion_on_view_time' => get_string('completiondetail:_on_view_time', 'videotime', $timespent),
            'completion_on_finish' => get_string('completiondetail:_on_finish', 'videotime'),
            'completion_on_percent' => get_string('completiondetail:_on_percent', 'videotime', $percentspent),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        if (!videotime_has_pro()) {
            return [
                'completionview',
            ];
        }

        return [
            'completionview',
            'completion_on_finish',
            'completion_on_percent',
            'completion_on_view_time',
            'completion_hide_detail',
            'completionpassgrade',
            'completionusegrade',
        ];
    }
}
