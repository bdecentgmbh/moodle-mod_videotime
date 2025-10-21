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

use context_module;
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
     * Get data for template
     *
     * @return array
     */
    public function export_for_template(): array {
        global $DB, $OUTPUT;

        $record = $this->get_instance()->to_record();
        $context = $this->get_instance()->get_context();

        $lastupdate = $DB->get_field('videotimetab_texttrack', 'lastupdate', ['videotime' => $record->id]);
        if (
            ($lastupdate < $record->timemodified)
            || $lastupdate < $DB->get_field('videotime_vimeo_video', 'modified_time', ['link' => $record->vimeo_url])
            || $DB->get_records_sql(
                "SELECT f.id
                   FROM {files} f
                   JOIN {videotime_track} t ON t.id = f.itemid
                  WHERE t.videotime = :videotime
                        AND f.timecreated > :lastupdate",
                [
                    'videotime' => $record->id,
                    'lastupdate' => $lastupdate,
                ]
            )
        ) {
            $this->update_tracks();
        }

        $texttracks = $DB->get_records_sql(
            "SELECT t.id AS trackid,
                        t.srclang AS lang
               FROM {videotime_track} t
          LEFT JOIN {files} f ON f.itemid = t.id
              WHERE f.component = 'videotimetab_texttrack'
                    AND f.filearea = 'texttrack'
                    AND f.id IS NULL",
            ['videotime' => $record->id]
        );

        $show = true;
        $query = optional_param('q', '', PARAM_TEXT);
        $localtracks = $DB->get_records('videotime_track', ['videotime' => $record->id]);
        foreach ($DB->get_records('videotimetab_texttrack_track', ['videotime' => $record->id]) as $track) {
            $localtrack = $localtracks[$track->uri] ?? null;
            if (!empty($localtrack) && empty($localtrack->visible) && !has_capability('moodle/course:managefiles', $context)) {
                continue;
            }
            $captions = $DB->get_records('videotimetab_texttrack_text', ['track' => $track->id], 'starttime, endtime');
            foreach ($captions as $caption) {
                $caption->lines = array_map(function ($text) use ($query) {
                    $text = s($text);
                    if ($query) {
                        $text = str_replace($query, "<span class=\"text-secondary\">$query</span>", s($text));
                    }
                    return ['text' => $text];
                }, explode("\n", $caption->text));
                $caption->starttimedisplay = preg_replace('/\\..*/', '', $caption->starttime);
                $caption = (array) $captions;
            }
            $track->captions = array_values($captions);
            $track->show = $show;
            $track->hidden = !empty($localtrack) && empty($localtrack->visible);
            $track->trackid = (int)$track->uri;
            $track->langname = $this->get_language_name(preg_replace('/-\\d+$|-x-autogen/', '', $track->lang));
            $track->autogen = strpos($track->lang, '-x-autogen');
            $track->label = $localtrack->label ?? '';
            $track->iscaption = ($track->type == 'captions');
            $show = false;
            $texttracks[] = $track;
        }

        $instance = $this->get_instance();
        $context = $instance->get_context();
        return [
            'texttracks' => $texttracks,
            'showselector' => count($texttracks) > 1,
            'canedit' => videotime_has_repository() && has_capability('moodle/course:managefiles', $context),
        ];
    }

    /**
     * Get update the stored track information
     */
    public function update_tracks() {
        global $DB;

        if (!videotime_has_repository()) {
            return;
        }

        $record = $this->get_instance()->to_record();
        \videotimeplugin_repository\texttrack::update_tracks($record);
    }

    /**
     * Saves a settings in database
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_texttrack)) {
            $DB->delete_records('videotimetab_texttrack', [
                'videotime' => $data->id,
            ]);
        } else if (!$DB->get_record('videotimetab_texttrack', ['videotime' => $data->id])) {
            $DB->insert_record('videotimetab_texttrack', [
                'videotime' => $data->id,
                'lastupdate' => 0,
            ]);
        }
    }

    /**
     * Delete settings in database
     *
     * @param  int $id
     */
    public static function delete_settings(int $id) {
        global $DB;

        if ($trackids = $DB->get_fieldset_select('videotimetab_texttrack_track', 'id', 'videotime = ?', [$id])) {
            [$sql, $params] = $DB->get_in_or_equal($trackids);
            $DB->delete_records_select('videotimetab_texttrack_text', "track $sql", $params);
            $DB->delete_records('videotimetab_texttrack_track', ['videotime' => $id]);
        }

        $DB->delete_records('videotimetab_texttrack', [
            'videotime' => $id,
        ]);
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
            $defaultvalues['enable_texttrack'] = $DB->record_exists('videotimetab_texttrack', ['videotime' => $instance]);
        }
        $draftitemid = file_get_submitted_draft_itemid('captions');
        $cm = get_coursemodule_from_instance('videotime', $instance);
        $context = context_module::instance($cm->id);
        file_prepare_draft_area(
            $draftitemid,
            $context->id,
            'videotimetab_texttrack',
            'captions',
            0,
            []
        );
        $defaultvalues['captions'] = $draftitemid;
    }

    /**
     * Whether tab is enabled and visible
     *
     * @return bool
     */
    public function is_visible(): bool {
        global $DB;

        $record = $this->get_instance()->to_record();
        return videotime_has_repository() && $this->is_enabled() && $DB->record_exists('videotimetab_texttrack', [
            'videotime' => $record->id,
        ]);
    }

    /**
     * Convert language code to string
     *
     * @param string $code
     * @return string
     */
    protected function get_language_name(string $code): string {
        $languages = get_string_manager()->get_list_of_languages();
        if (key_exists($code, $languages)) {
            return $languages[$code];
        }
        return $code;
    }

    /**
     * List of missing dependencies needed for plugin to be enabled
     */
    public static function added_dependencies() {
        global $OUTPUT;
        if (videotime_has_repository()) {
            return '';
        }
        return $OUTPUT->render_from_template('videotimetab_texttrack/upgrade', []);
    }
}
