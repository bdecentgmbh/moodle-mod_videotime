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
     * Returns recordset containing required data attributes for indexing.
     *
     * @param number $modifiedfrom
     * @return \moodle_recordset
     */
    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        return $this->get_document_recordset($modifiedfrom);
    }

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

    /**
     * This function available in the moodle 3.4 source.
     *
     * Helper function that gets SQL useful for restricting a search query given a passed-in
     * context.
     *
     * The SQL returned will be zero or more JOIN statements, surrounded by whitespace, which act
     * as restrictions on the query based on the rows in a module table.
     *
     * You can pass in a null or system context, which will both return an empty string and no
     * params.
     *
     * Returns an array with two nulls if there can be no results for the activity within this
     * context (e.g. it is a block context).
     *
     * If named parameters are used, these will be named gcrs0, gcrs1, etc. The table aliases used
     * in SQL also all begin with gcrs, to avoid conflicts.
     *
     * @param \context|null $context Context to restrict the query
     * @param string $modname Name of module e.g. 'forum'
     * @param string $modtable Alias of table containing module id
     * @param int $paramtype Type of SQL parameters to use (default question mark)
     * @return array Array with SQL and parameters; both null if no need to query
     * @throws \coding_exception If called with invalid params
     */
    protected function get_context_restriction_sql(\context $context = null, $modname, $modtable,
            $paramtype = SQL_PARAMS_QM) {
        global $DB;

        if (!$context) {
            return ['', []];
        }

        switch ($paramtype) {
            case SQL_PARAMS_QM:
                $param1 = '?';
                $param2 = '?';
                $param3 = '?';
                $key1 = 0;
                $key2 = 1;
                $key3 = 2;
                break;
            case SQL_PARAMS_NAMED:
                $param1 = ':gcrs0';
                $param2 = ':gcrs1';
                $param3 = ':gcrs2';
                $key1 = 'gcrs0';
                $key2 = 'gcrs1';
                $key3 = 'gcrs2';
                break;
            default:
                throw new \coding_exception('Unexpected $paramtype: ' . $paramtype);
        }

        $params = [];
        switch ($context->contextlevel) {
            case CONTEXT_SYSTEM:
                $sql = '';
                break;

            case CONTEXT_COURSECAT:
                // Find all activities of this type within the specified category or any
                // sub-category.
                $pathmatch = $DB->sql_like('gcrscc2.path', $DB->sql_concat('gcrscc1.path', $param3));
                $sql = " JOIN {course_modules} gcrscm ON gcrscm.instance = $modtable.id
                              AND gcrscm.module = (SELECT id FROM {modules} WHERE name = $param1)
                         JOIN {course} gcrsc ON gcrsc.id = gcrscm.course
                         JOIN {course_categories} gcrscc1 ON gcrscc1.id = $param2
                         JOIN {course_categories} gcrscc2 ON gcrscc2.id = gcrsc.category AND
                              (gcrscc2.id = gcrscc1.id OR $pathmatch) ";
                $params[$key1] = $modname;
                $params[$key2] = $context->instanceid;
                // Note: This param is a bit annoying as it obviously never changes, but sql_like
                // throws a debug warning if you pass it anything with quotes in, so it has to be
                // a bound parameter.
                $params[$key3] = '/%';
                break;

            case CONTEXT_COURSE:
                // Find all activities of this type within the course.
                $sql = " JOIN {course_modules} gcrscm ON gcrscm.instance = $modtable.id
                              AND gcrscm.course = $param1
                              AND gcrscm.module = (SELECT id FROM {modules} WHERE name = $param2) ";
                $params[$key1] = $context->instanceid;
                $params[$key2] = $modname;
                break;

            case CONTEXT_MODULE:
                // Find only the specified activity of this type.
                $sql = " JOIN {course_modules} gcrscm ON gcrscm.instance = $modtable.id
                              AND gcrscm.id = $param1
                              AND gcrscm.module = (SELECT id FROM {modules} WHERE name = $param2) ";
                $params[$key1] = $context->instanceid;
                $params[$key2] = $modname;
                break;

            case CONTEXT_BLOCK:
            case CONTEXT_USER:
                // These contexts cannot contain any activities, so return null.
                return [null, null];

            default:
                throw new \coding_exception('Unexpected contextlevel: ' . $context->contextlevel);
        }

        return [$sql, $params];
    }

    /**
     * Returns the module name.
     *
     * @return string
     */
    protected function get_module_name() {
        return substr($this->componentname, 4);
    }
}
