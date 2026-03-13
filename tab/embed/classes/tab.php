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
 * @package     videotimetab_embed
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_embed;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Embed tab — renders an admin-configured URL template as an iframe.
 *
 * The URL template may contain placeholders like {username}, {email}, etc.
 * Each placeholder present in the template is replaced with the corresponding
 * rawurlencode()-encoded value. Placeholders not present in the template are
 * simply ignored.
 *
 * @package videotimetab_embed
 */
class tab extends \mod_videotime\local\tabs\tab {

    /**
     * Get tab panel content.
     *
     * @return string
     */
    public function get_tab_content(): string {
        global $OUTPUT, $USER;

        $instance = $this->get_instance();
        $cm       = get_coursemodule_from_instance('videotime', $instance->id, $instance->course);
        $course   = get_course($instance->course);
        $videoid  = mod_videotime_get_vimeo_id_from_link($instance->vimeo_url ?? '') ?? '';

        $template = (string) get_config('videotimetab_embed', 'embedurl');

        $src = str_replace(
            ['{username}', '{firstname}', '{email}',
             '{courseshortname}', '{coursefullname}', '{courseidnumber}',
             '{vtidnumber}', '{videoid}'],
            array_map('rawurlencode', [
                $USER->username    ?? '',
                $USER->firstname   ?? '',
                $USER->email       ?? '',
                $course->shortname ?? '',
                $course->fullname  ?? '',
                $course->idnumber  ?? '',
                $cm->idnumber      ?? '',
                $videoid,
            ]),
            $template
        );

        return $OUTPUT->render_from_template('videotimetab_embed/tab', ['src' => $src]);
    }

    /**
     * Whether the tab is enabled and visible.
     *
     * Returns false if the tab is not enabled for this instance, or if no
     * embed URL template has been configured in admin settings.
     *
     * @return bool
     */
    public function is_visible(): bool {
        global $DB;

        if (!$this->is_enabled()) {
            return false;
        }
        if (!trim((string) get_config('videotimetab_embed', 'embedurl'))) {
            return false;
        }
        $record = $this->get_instance()->to_record();
        return $DB->record_exists('videotimetab_embed', ['videotime' => $record->id]);
    }

    /**
     * Defines the additional form fields.
     *
     * @param moodle_form $mform form to modify
     */
    public static function add_form_fields($mform) {
        $mform->addElement(
            'advcheckbox',
            'enable_embed',
            get_string('pluginname', 'videotimetab_embed'),
            get_string('showtab', 'videotime')
        );
        $mform->setDefault('enable_embed', get_config('videotimetab_embed', 'default'));
        $mform->disabledIf('enable_embed', 'enabletabs');

        $mform->addElement('text', 'embedtab_name', get_string('embedtab_name', 'videotimetab_embed'));
        $mform->setType('embedtab_name', PARAM_TEXT);
        $mform->hideIf('embedtab_name', 'enable_embed', 'notchecked');

        $mform->addElement('static', 'tab_separator_embed', '', '<hr class="mt-1 mb-2">');
        $mform->hideIf('tab_separator_embed', 'enable_embed', 'notchecked');
    }

    /**
     * Saves settings in database.
     *
     * @param stdClass $data Form data with values to save
     */
    public static function save_settings(stdClass $data) {
        global $DB;

        if (empty($data->enable_embed)) {
            $DB->delete_records('videotimetab_embed', ['videotime' => $data->id]);
        } else if ($record = $DB->get_record('videotimetab_embed', ['videotime' => $data->id])) {
            $record->name = $data->embedtab_name ?? '';
            $DB->update_record('videotimetab_embed', $record);
        } else {
            $DB->insert_record('videotimetab_embed', [
                'videotime' => $data->id,
                'name'      => $data->embedtab_name ?? '',
            ]);
        }
    }

    /**
     * Delete settings in database.
     *
     * @param int $id videotime instance id
     */
    public static function delete_settings(int $id) {
        global $DB;

        $DB->delete_records('videotimetab_embed', ['videotime' => $id]);
    }

    /**
     * Prepares the form before data are set.
     *
     * @param array $defaultvalues
     * @param int   $instance
     */
    public static function data_preprocessing(array &$defaultvalues, int $instance) {
        global $DB;

        if (empty($instance)) {
            $defaultvalues['enable_embed'] = get_config('videotimetab_embed', 'default');
        } else if ($record = $DB->get_record('videotimetab_embed', ['videotime' => $instance])) {
            $defaultvalues['enable_embed']  = 1;
            $defaultvalues['embedtab_name'] = $record->name;
        } else {
            $defaultvalues['enable_embed'] = 0;
        }
    }

    /**
     * Get label for tab, using the custom name if one has been set.
     *
     * @return string
     */
    public function get_label(): string {
        if ($label = $this->get_record()->name ?? '') {
            return $label;
        }
        return parent::get_label();
    }

    /**
     * List of missing dependencies needed for plugin to be enabled.
     *
     * Returns an upgrade-prompt HTML snippet when Video Time Pro is not
     * installed, causing the base class to treat this tab as unavailable.
     *
     * @return string
     */
    public static function added_dependencies() {
        global $OUTPUT;
        if (videotime_has_pro()) {
            return '';
        }
        return $OUTPUT->render_from_template('videotimetab_embed/upgrade', []);
    }
}
