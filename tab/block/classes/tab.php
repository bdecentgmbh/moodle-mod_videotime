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
 * @package     videotimetab_block
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_block;

use context_module;
use mod_videotime\output\renderer;
use mod_videotime\videotime_instance;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Tab.
 *
 * @package videotimetab_block
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab panel content
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $DB, $OUTPUT, $PAGE;

        $instance = $this->get_instance();

        if (!empty($PAGE->cm)) {
            return  $OUTPUT->render_from_template('videotimetab_block/tab', [
                'pre' => $OUTPUT->custom_block_region('mod_poster-pre'),
                'post' => $OUTPUT->custom_block_region('mod_poster-post'),
            ]);
        }

        // Need to add block areas, but can not do it on existing page.
        $page = new \moodle_page();
        $page->set_cm($instance->get_cm());
        $page->set_context($instance->get_context());
        $page->set_url('/mod/videotime/view.php', array('id' => $instance->get_cm()->id));
        $page->set_title('title');
        $page->set_heading('heading');
        $page->set_activity_record($instance->to_record());
        // Define the custom block regions we want to use at the poster view page.
        // Region names are limited to 16 characters.
        $page->blocks->add_region('mod_poster-pre', true);
        $page->blocks->add_region('mod_poster-post', true);

        $output = new renderer($page, RENDERER_TARGET_GENERAL);
        $head = $output->header();

        return  $output->render_from_template('videotimetab_block/tab', [
            'pre' => $output->custom_block_region('mod_poster-pre'),
            'post' => $output->custom_block_region('mod_poster-post'),
        ]);
        return 'block content';

        $instance = $this->get_instance();
        if ($record = $this->get_record()) {
            $cm = get_coursemodule_from_instance('videotime', $instance->id, $instance->course);
            $context = context_module::instance($cm->id);
            return format_text(file_rewrite_pluginfile_urls(
                $record->text,
                'pluginfile.php',
                $context->id,
                'videotimetab_block',
                'text',
                0
            ), $record->format);
        } else {
            return '';
        }
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
        $mform->addElement('advcheckbox', 'enable_block', get_string('pluginname', 'videotimetab_block'),
            get_string('showtab', 'videotime'));
        $mform->setDefault('enable_block', get_config('videotimetab_block', 'default'));
        $mform->disabledIf('enable_block', 'enabletabs');
    }

    /**
     * Whether tab is enabled and visible
     *
     * @return bool
     */
    public function is_visible(): bool {
        global $DB;

        $record = $this->get_instance()->to_record();
        return $this->is_enabled() && $DB->record_exists('videotimetab_block', array(
            'videotime' => $record->id
        ));
    }

    /**
     * Saves a settings in database
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_block)) {
            $DB->delete_records('videotimetab_block', array(
                'videotime' => $data->id,
            ));
        } else {
            if (!$record = $DB->get_record('videotimetab_block', array('videotime' => $data->id))) {
                $DB->insert_record('videotimetab_block', array(
                    'videotime' => $data->id,
                ));
            }
        }
    }

    /**
     * Delete settings in database
     *
     * @param  int $id
     */
    public static function delete_settings(int $id) {
        global $DB;

        $DB->delete_records('videotimetab_block', array(
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
        global $COURSE, $DB;

        if (empty($instance)) {
            $defaultvalues['enable_block'] = get_config('videotimetab_block', 'default');
        } else if ($record = $DB->get_record('videotimetab_block', array('videotime' => $instance))) {
            $defaultvalues['enable_block'] = 1;
        } else {
            $defaultvalues['enable_block'] = 0;
        }
    }

    /**
     * Hook to set up page by adding blocks etc.
     */
    public function setup_page() {
        global $PAGE;

        if ($PAGE->cm && $this->is_visible()) {
            $PAGE->blocks->add_region('mod_poster-pre', true);
            $PAGE->blocks->add_region('mod_poster-post', true);
        }
    }
}
