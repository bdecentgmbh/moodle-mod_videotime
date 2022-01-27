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
use moodle_url;
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

        if ($PAGE->user_can_edit_blocks() && $PAGE->user_is_editing()) {
            $haspre = true;
            $haspost = true;
        } else {
            $haspre = $PAGE->blocks->region_has_content('videotime-pre', $this);
            $haspost = $PAGE->blocks->region_has_content('videotime-post', $this);
        }

        if ($PAGE->cm) {
            return  $OUTPUT->render_from_template('videotimetab_block/tab', [
                'haspost' => $haspost,
                'haspre' => $haspre,
                'post' => $OUTPUT->custom_block_region('videotime-post'),
                'pre' => $OUTPUT->custom_block_region('videotime-pre'),
            ]);
        }

        // We do not want to try editing or the course page.
        if ($PAGE->user_is_editing()) {
            $url = new moodle_url('/mod/videotime/view.php', array('id' => $instance->get_cm()->id));
            return  $OUTPUT->render_from_template('videotimetab_block/edit_label', [
                'id' => $instance->id,
                'url' => $url->out(),
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
        $page->blocks->add_region('videotime-pre', true);
        $page->blocks->add_region('videotime-post', true);

        $page->blocks->load_blocks();

        $output = new renderer($page, RENDERER_TARGET_GENERAL);

        return  $output->render_from_template('videotimetab_block/tab', [
            'haspost' => $haspost,
            'haspre' => $haspre,
            'pre' => $output->custom_block_region('videotime-pre'),
            'post' => $output->custom_block_region('videotime-post'),
        ]);
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

        $mform->addElement('text', 'blocktab_name', get_string('blocktab_name', 'videotimetab_block'));
        $mform->setType('blocktab_name', PARAM_TEXT);
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
        } else if ($record = $DB->get_record('videotimetab_block', array('videotime' => $data->id))) {
            $record->name = $data->blocktab_name;
            $DB->update_record('videotimetab_block', $record);
        } else {
            $DB->insert_record('videotimetab_block', array(
                'videotime' => $data->id,
                'name' => $data->blocktab_name,
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
            $defaultvalues['blocktab_name'] = $record->name;
        } else {
            $defaultvalues['enable_block'] = 0;
        }
    }

    /**
     * Hook to set up page by adding blocks etc.
     */
    public function setup_page() {
        global $OUTPUT, $PAGE;

        if ($PAGE->cm && $this->is_visible()) {
            $PAGE->blocks->add_region('videotime-pre', true);
            $PAGE->blocks->add_region('videotime-post', true);
            if ($PAGE->user_allowed_editing()) {
                $PAGE->set_button($OUTPUT->edit_button($PAGE->url));
                $PAGE->blocks->set_default_region('videotime-pre');
                $PAGE->theme->addblockposition = BLOCK_ADDBLOCK_POSITION_DEFAULT;
            }
        }
    }

    /**
     * Get label for tab
     *
     * @return string
     */
    public function get_label(): string {
        if ($label = $this->get_record()->name) {
            return $label;
        }

        return parent::get_label();
    }
}
