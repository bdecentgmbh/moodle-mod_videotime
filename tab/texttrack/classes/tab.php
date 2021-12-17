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

use stdClass;

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_texttrack
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab panel content
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $OUTPUT;

        $data = $this->export_for_template();

        return $OUTPUT->render_from_template('videotimetab_texttrack/text_tab', $data);
    }

    /**
     * Parse track file to array of cues
     *
     * @param string $track Text track file contents
     * @return array
     */
    public function parse_texttrack(string $track): array {
        $matches = array();
        preg_match_all('/([.:0-9]+)  *-->  *([.:0-9]+)(.*?)^$/ms', $track . '\n', $matches);

        return array_map(function($starttime, $endtime, $text) {
            return array(
                'starttime' => $starttime,
                'endtime' => $endtime,
                'lines' => array_map(function($text) {
                    return array('text' => $text);
                }, explode("\n", $text)),
                'text' => $text,
            );
        }, $matches[1], $matches[2], $matches[3]);
    }

    /**
     * Get data for template
     *
     * @return array
     */
    public function export_for_template(): array {
        global $DB;

        $record = $this->get_instance()->to_record();

        if ($DB->get_field('videotimetab_texttrack', 'lastupdate', array('videotime' => $record->id)) < $record->timemodified) {
            $this->update_tracks();
        }

        $texttracks = [];
        $show = true;
        foreach ($DB->get_records('videotimetab_texttrack_track', array('videotime' => $record->id)) as $track) {
            $captions = $DB->get_records('videotimetab_texttrack_text', array('track' => $track->id), 'starttime, endtime');
            foreach ($captions as $caption) {
                $caption->lines = array_map(function($text) {
                    return array('text' => $text);
                }, explode("\n", $caption->text));
                $caption->starttimedisplay = preg_replace('/\\..*/', '', $caption->starttime);
                $caption = (array) $captions;
            }
            $track->captions = array_values($captions);
            $track->show = $show;
            $show = false;
            $texttracks[] = $track;
        }

        return array(
            'texttracks' => $texttracks,
        );
    }

    /**
     * Get update the stored track information
     */
    public function update_tracks() {
        global $DB;

        $api = new \videotimeplugin_repository\api();
        $record = $this->get_instance()->to_record();
        $endpoint = '/videos/' . mod_videotime_get_vimeo_id_from_link($record->vimeo_url) . '/texttracks';
        $request = $api->request($endpoint);
        if ($request['status'] != 200 || empty($request['body']['data'])) {
            return;
        }

        $DB->set_field('videotimetab_texttrack', 'lastupdate', time(), array('videotime' => $record->id));
        if ($trackids = $DB->get_fieldset_select('videotimetab_texttrack_track', 'id',  'videotime = ?', array($record->id))) {
            list($sql, $params) = $DB->get_in_or_equal($trackids);
            $DB->delete_records_select('videotimetab_texttrack_text', "track $sql", $params);
            $DB->delete_records('videotimetab_texttrack_track', array('videotime' => $record->id));
        }

        foreach ($request['body']['data'] as $texttrack) {
            if ($texttrack['active']) {
                $trackid = $DB->insert_record('videotimetab_texttrack_track', array(
                    'videotime' => $record->id,
                    'lang' => $texttrack['language'],
                    'uri' => $texttrack['uri'],
                    'type' => $texttrack['type'],
                    $texttrack['link'])
                );
                foreach ($this->parse_texttrack(file_get_contents($texttrack['link'])) as $text) {
                    $text['track'] = $trackid;
                    $DB->insert_record('videotimetab_texttrack_text', $text);
                }
            }
        }
    }

    /**
     * Saves a settings in database
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_texttrack)) {
            $DB->delete_records('videotimetab_texttrack', array(
                'videotime' => $data->id,
            ));
        } else if (!$DB->get_record('videotimetab_texttrack', array('videotime' => $data->id))) {
            $DB->insert_record('videotimetab_texttrack', array(
                'videotime' => $data->id,
                'lastupdate' => 0,
            ));
        }
    }

    /**
     * Delete settings in database
     *
     * @param  int $id
     */
    public static function delete_settings(int $id) {
        global $DB;

        if ($trackids = $DB->get_fieldset_select('videotimetab_texttrack_track', 'id',  'videotime = ?', array($record->id))) {
            list($sql, $params) = $DB->get_in_or_equal($trackids);
            $DB->delete_records_select('videotimetab_texttrack_text', "track $sql", $params);
            $DB->delete_records('videotimetab_texttrack_track', array('videotime' => $record->id));
        }

        $DB->delete_records('videotimetab_texttrack', array(
            'videotime' => $id,
        ));
    }

    /**
     * Prepares the form before data are set
     *
     * @param  array $defaultvalues
     * @param  int $instance
     */
    public static function data_preprocessing(array &$defaultvalues, int $instance) {
        global $DB;

        if (empty($instance)) {
            $defaultvalues['enable_texttrack'] = get_config('videotimetab_texttrack', 'default');
        } else {
            $defaultvalues['enable_texttrack'] = $DB->record_exists('videotimetab_texttrack', array('videotime' => $instance));
        }
    }

    /**
     * Whether tab is enabled and visible
     *
     * @return bool
     */
    public function is_visible(): bool {
        global $DB;

        $record = $this->get_instance()->to_record();
        return $this->is_enabled() && $DB->record_exists('videotimetab_texttrack', array(
            'videotime' => $record->id
        ));
    }
}
