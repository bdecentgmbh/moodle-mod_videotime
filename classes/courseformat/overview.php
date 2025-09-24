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

namespace mod_videotime\courseformat;

use cm_info;
use core\url;
use core\output\local\properties\text_align;
use core_courseformat\local\overview\overviewitem;
use core_courseformat\output\local\overview\overviewaction;

/**
 * Video Time overview integration class.
 *
 * @package    mod_videotime
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class overview extends \core_courseformat\activityoverviewbase {
    #[\Override]
    public function get_extra_overview_items(): array {
        return [
            'totalviews' => $this->get_extra_totalviews_overview(),
        ];
    }

    #[\Override]
    public function get_actions_overview(): ?overviewitem {
        if (!videotime_has_pro() || !has_capability('mod/videotime:view_report', $this->context)) {
            return null;
        }

        $viewscount = 1;
        $url = new url('/mod/videotime/report.php', ['id' => $this->cm->id, 'mode' => 'approval']);
        $text = get_string('view_report', 'mod_videotime');

        $content = new overviewaction(
            url: $url,
            text: $text,
            badgevalue: null,
            badgetitle: null
        );

        return new overviewitem(
            name: get_string('actions'),
            value: $viewscount,
            content: $content,
            textalign: text_align::CENTER,
        );
    }

    /**
     * Get the "Total views" overview item.
     *
     * @return overviewitem The overview item.
     */
    private function get_extra_totalviews_overview(): overviewitem {
        global $DB, $USER;

        $columnheader = get_string('views', 'mod_videotime');

        $params = ['module_id' => $this->cm->id];
        if (!has_capability('moodle/course:viewparticipants', $this->context)) {
            $params['user_id'] = $USER->id;
        }
        $viewscount = $DB->get_field(
            \videotimeplugin_pro\session::TABLE,
            'COUNT(DISTINCT uuid)',
            $params
        );

        return new overviewitem(
            name: $columnheader,
            value: $viewscount,
            textalign: text_align::END,
        );
    }
}
