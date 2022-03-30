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
 * Search area for mod_videotime activities.
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\search;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotime/lib.php');

use core_component;

/**
 * Search area for mod_videotime activities.
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class texttrack extends \core_search\base_mod {

    /**
     * Returns recordset containing required data for indexing text tracks.
     *
     * @param int $modifiedfrom timestamp
     * @param \context|null $context Optional context to restrict scope of returned results
     * @return moodle_recordset|null Recordset (or null if no results)
     */
    public function get_document_recordset($modifiedfrom = 0, \context $context = null) {
        global $DB;

        list ($contextjoin, $contextparams) = $this->get_context_restriction_sql(
                $context, 'videotime', 'v');
        if ($contextjoin === null) {
            return null;
        }

        if (
            !videotime_has_repository()
            || !key_exists('texttrack', core_component::get_plugin_list('videotimetab'))
        ) {
            // This is a hack because returning null does not work.
            return $DB->get_recordset('videotime', ['id' => 0]);
        }

        $sql = "SELECT te.id, te.text, tr.lang, te.starttime, v.name, v.timemodified, v.course, v.id AS moduleinstanceid
                  FROM {videotime} v
                  JOIN {videotimetab_texttrack_track} tr ON v.id = tr.videotime
                  JOIN {videotimetab_texttrack_text} te ON tr.id = te.track
          $contextjoin
                 WHERE v.timemodified >= ? ORDER BY v.timemodified ASC";
        return $DB->get_recordset_sql($sql, array_merge($contextparams, [$modifiedfrom]));
    }

    /**
     * Returns the document associated with this activity.
     *
     * Overwriting base_activity method to include text tracks
     *
     * @param stdClass $record
     * @param array    $options
     * @return \core_search\document
     */
    public function get_document($record, $options = array()) {

        try {
            $cm = $this->get_cm($this->get_module_name(), $record->moduleinstanceid, $record->course);
            $context = \context_module::instance($cm->id);
        } catch (\dml_missing_record_exception $ex) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving ' . $this->areaid . ' ' . $record->moduleinstanceid .
                ' document, not all required data is available: ' .  $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $ex) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving ' . $this->areaid . ' ' . $record->moduleinstanceid .
                ' document: ' . $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

        // Prepare associative array with data from DB.
        $doc = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);
        $doc->set('title', content_to_text($record->name, false));
        $doc->set('content', $record->text);
        $doc->set('description1', $record->starttime);
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $record->course);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', $record->timemodified);

        return $doc;
    }

    /**
     * Returns true if this area uses file indexing.
     *
     * @return bool
     */
    public function uses_file_indexing() {
        return false;
    }

    /**
     * Link to the videotime.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_context_url(\core_search\document $doc) {
        global $DB;

        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        $itemid = $doc->get('itemid');
        $record = $DB->get_record_sql('SELECT te.*, tr.lang, tr.videotime
                                         FROM {videotimetab_texttrack_text} te
                                         JOIN {videotimetab_texttrack_track} tr ON te.track = tr.id
                                        WHERE te.id = :itemid', array('itemid' => $itemid));

        $url = new \moodle_url('/mod/videotime/view.php', array(
            'id' => $contextmodule->instanceid,
            'q' => optional_param('q', '', PARAM_TEXT),
        ));

        if (!empty($record)) {
            $url->param('time', $record->starttime);
            $url->param('lang', $record->lang);
        }

        $url->set_anchor('texttrack-' . $record->videotime);
        return $url;
    }

    /**
     * Whether the user can access the document or not.
     *
     * @throws \dml_missing_record_exception
     * @throws \dml_exception
     * @param int $id Video Time entry id
     * @return bool
     */
    public function check_access($id) {
        global $USER;

        return \core_search\manager::ACCESS_GRANTED;
    }

    /**
     * Link to Video time instance
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_doc_url(\core_search\document $doc) {
        global $USER;

        $contextmodule = \context::instance_by_id($doc->get('contextid'));

        $docparams = array('id' => $contextmodule->instanceid);

        return new \moodle_url('/mod/videotime/view.php', $docparams);
    }
}
