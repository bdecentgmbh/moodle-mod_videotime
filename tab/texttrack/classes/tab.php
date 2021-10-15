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
 * Tab.
 *
 * @package     videotimetab_texttrack
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_texttrack;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_texttrack
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab name for ids
     *
     * @return string
     */
    public function get_name(): string {
        return 'transcript';
    }

    /**
     * Get label for tab
     *
     * @return string
     */
    public function get_label(): string {
        return get_string('tabtranscript', 'videotime');
    }

    /**
     * Get tab panel content
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $OUTPUT;

        $data = $this->export_for_template();

        return $OUTPUT->render_from_template('mod_videotime/text_tab', $data);
    }

    /**
     * Parse track file to array of cues
     *
     * @param string $track Text track file contents
     * @return array
     */
    public function parse_texttrack(string $track): array {
        $matches = array();
        preg_match_all('/([.:0-9]+)  *-->  *([.:0-9]+)(.*?)^$/ms', $track, $matches);

        return array_map(function($starttime, $endtime, $text) {
            return array(
                'starttime' => $starttime,
                'endtime' => $endtime,
                'lines' => array_map(function($text) {
                    return array('text' => $text);
                }, explode("\n", $text)),
            );
        }, $matches[1], $matches[2], $matches[3]);
    }

    /**
     * Get data for template
     *
     * @return array
     */
    public function export_for_template(): array {
        $api = new \videotimeplugin_repository\api();
        $record = $this->get_instance()->to_record();
        $endpoint = '/videos/' . mod_videotime_get_vimeo_id_from_link($record->vimeo_url) . '/texttracks';
        $request = $api->request($endpoint);
        if ($request['status'] != 200 || empty($request['body']['data'])) {
            return array();
        }
        $texttracks = array();
        foreach ($request['body']['data'] as $texttrack) {
            $texttrack['captions'] = $this->parse_texttrack(file_get_contents($texttrack['link']));
            $texttracks[] = $texttrack;
        }
        return array(
            'texttracks' => $texttracks,
        );
    }
}
