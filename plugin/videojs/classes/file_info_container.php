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

namespace videotimeplugin_videojs;

use file_info;
use file_info_stored;

/**
 * File browsing support class.
 *
 * @package   videotimeplugin_videojs
 * @copyright 2025 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class file_info_container extends file_info {
    /**
     * Constructor
     *
     * @param file_browser $browser The file_browser instance
     * @param stdClass $course Course object
     * @param stdClass $cm course Module object
     * @param stdClass $context Module context
     * @param array $areas Available file areas
     * @param string $filearea File area to browse
     */
    public function __construct(
        $browser,
        /** @var stdClass $course Course object */
        protected $course,
        /** @var stdClass $cm Course module object */
        protected $cm,
        $context,
        /** @var array Available file areas */
        protected $areas,
        /** @var string File area to browse */
        protected $filearea
    ) {
        parent::__construct($browser, $context);
    }

    /**
     * Returns list of standard virtual file/directory identification.
     * The difference from stored_file parameters is that null values
     * are allowed in all fields
     * @return array with keys contextid, filearea, itemid, filepath and filename
     */
    public function get_params() {
        return ['contextid' => $this->context->id,
                     'component' => 'videotimeplugin_videojs',
                     'filearea'  => $this->filearea,
                     'itemid'    => null,
                     'filepath'  => null,
                     'filename'  => null];
    }

    /**
     * Returns localised visible name.
     * @return string
     */
    public function get_visible_name() {
        return $this->areas[$this->filearea];
    }

    /**
     * Can I add new files or directories?
     * @return bool
     */
    public function is_writable() {
        return false;
    }

    /**
     * Is directory?
     * @return bool
     */
    public function is_directory() {
        return true;
    }

    /**
     * Returns list of children.
     * @return array of file_info instances
     */
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    /**
     * Help function to return files matching extensions or their count
     *
     * @param string|array $extensions
     * @param bool|int $countonly
     * @param bool $returnemptyfolders
     * @return array|int array of file_info instances or the count
     */
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        global $DB;
        throw new \moodle_exception('x');

        $params = [
            'contextid' => $this->context->id,
            'component' => 'videotimeplugin_videojs',
            'filearea' => $this->filearea,
        ];
        $sql = 'SELECT DISTINCT itemid
                  FROM {files}
                 WHERE contextid = :contextid
                   AND component = :component
                   AND filearea = :filearea';

        if (!$returnemptyfolders) {
            $sql .= ' AND filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }

        [$sql2, $params2] = $this->build_search_files_sql($extensions);
        $sql .= ' ' . $sql2;
        $params = array_merge($params, $params2);

        if ($countonly !== false) {
            $sql .= ' ORDER BY itemid DESC';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = [];
        foreach ($rs as $record) {
            if (
                ($child = $this->browser->get_file_info(
                    $this->context,
                    'videotimeplugin_videojs',
                    $this->filearea,
                    $record->itemid
                ))
                && ($returnemptyfolders || $child->count_non_empty_children($extensions))
            ) {
                $children[] = $child;
            }
            if ($countonly !== false && count($children) >= $countonly) {
                break;
            }
        }
        $rs->close();
        if ($countonly !== false) {
            return count($children);
        }
        return $children;
    }

    /**
     * Returns list of children which are either files matching the specified extensions
     * or folders that contain at least one such file.
     *
     * @param string|array $extensions
     * @return array
     */
    public function get_non_empty_children($extensions = '*') {
        return $this->get_filtered_children($extensions, false);
    }

    /**
     * Returns the number of children which are either files matching the specified extensions
     * or folders containing at least one such file.
     *
     * @param string|array $extensions
     * @param int $limit
     * @return int
     */
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        return $this->get_filtered_children($extensions, $limit);
    }

    /**
     * Returns parent file_info instance
     * @return file_info or null for root
     */
    public function get_parent() {
        return $this->browser->get_file_info($this->context);
    }

    /**
     * File browsing support for videotimeplugin_videojs file areas.
     *
     * @package     videotimeplugin_videojs
     * @category    files
     *
     * @param file_browser $browser
     * @param array $areas
     * @param stdClass $course
     * @param stdClass $cm
     * @param stdClass $context
     * @param string $filearea
     * @param int $itemid
     * @param string $filepath
     * @param string $filename
     * @return file_info Instance or null if not found.
     */
    public static function get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
        global $CFG, $DB, $USER;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return null;
        }

        if (!isset($areas[$filearea])) {
            return null;
        }

        if (is_null($itemid)) {
            return new static($browser, $course, $cm, $context, $areas, $filearea);
        }

        if (!$DB->get_record('videotime', ['id' => $cm->instance])) {
            return null;
        }

        $fs = get_file_storage();
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!($storedfile = $fs->get_file($context->id, 'videotimeplugin_videojs', $filearea, $itemid, $filepath, $filename))) {
            return null;
        }

        // Checks to see if the user can manage files or is the owner.
        if (!has_capability('moodle/course:managefiles', $context) && $storedfile->get_userid() != $USER->id) {
            return null;
        }

        $urlbase = $CFG->wwwroot . '/pluginfile.php';

        return new file_info_stored(
            $browser,
            $context,
            $storedfile,
            $urlbase,
            get_string($filearea, 'videotimeplugin_videojs'),
            true,
            true,
            false,
            false
        );
    }
}
