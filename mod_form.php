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
 * The main mod_videotime configuration form.
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\output\next_activity_button;
use mod_videotime\videotime_instance;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_videotime
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videotime_mod_form extends moodleform_mod {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $COURSE, $PAGE, $DB;

        $mform = $this->_form;

        if (!videotime_has_pro()) {
            $mform->addElement('static', '', '', html_writer::link(
                new moodle_url('https://link.bdecent.de/videotimepro1'),
                html_writer::img(
                    'https://link.bdecent.de/videotimepro1/image.jpg',
                    '',
                    ['width' => '100%', 'class' => 'img-responsive', 'style' => 'max-width:700px']
                )
            ));
        } else {
            $PAGE->requires->js_call_amd(
                'mod_videotime/mod_form',
                'init'
            );
        }

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        if (videotime_has_pro() && videotime_has_repository()) {
            $needssetup = false;
            try {
                $api = new \videotimeplugin_repository\api();
            } catch (\videotimeplugin_repository\exception\api_not_configured $e) {
                $needssetup = true;
            } catch (\videotimeplugin_repository\exception\api_not_authenticated $e) {
                $needssetup = true;
            }

            $group = [];
            $group[] = $mform->createElement('text', 'vimeo_url', get_string('vimeo_url', 'videotime'));
            if (!$needssetup) {
                $group[] = $mform->createElement('button', 'pull_from_vimeo', get_string('pull_from_vimeo', 'videotime'));
            }
            $mform->addGroup($group, 'vimeo_url_group', get_string('vimeo_url', 'videotime'), null, false);
            $mform->addHelpButton('vimeo_url_group', 'vimeo_url', 'videotime');
            $mform->setType('vimeo_url_group', PARAM_RAW);

            $group = [];
            if (!$needssetup) {
                $group[] = $mform->createElement('static', 'choose_video_label', '', '- or -');
                $group[] = $mform->createElement('button', 'choose_video', get_string('choose_video', 'videotime'));
            } else if (is_siteadmin()) {
                $group[] = $mform->createElement('static', 'choose_video_label', '', '- or -');
                $group[] = $mform->createElement(
                    'html',
                    html_writer::link(
                        new moodle_url('/mod/videotime/plugin/repository/overview.php'),
                        get_string('setup_repository', 'videotime')
                    )
                );
            }
            $mform->addGroup($group);

            $PAGE->requires->js_call_amd(
                'videotimeplugin_repository/mod_form',
                'init',
                [videotime_is_totara(), $this->context->id]
            );
        } else {
            $mform->addElement('text', 'vimeo_url', get_string('vimeo_url', 'videotime'), ['size' => 100]);
            $mform->addHelpButton('vimeo_url', 'vimeo_url', 'videotime');
        }

        $mform->setType('vimeo_url', PARAM_URL);

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('activity_name', 'mod_videotime'), ['size' => '64']);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'activity_name', 'mod_videotime');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $mform->addElement(
            'advcheckbox',
            'show_description_in_player',
            '',
            get_string('show_description_in_player', 'videotime')
        );
        $mform->setDefault('show_description_in_player', get_config('videotime', 'show_description_in_player'));

        // Video Time video description.
        $mform->addElement(
            'editor',
            'video_description',
            get_string('video_description', 'videotime'),
            ['rows' => 10],
            [
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'noclean' => true,
                'context' => $this->context,
                'subdirs' => true,
            ],
        );
        $mform->setType('video_description', PARAM_RAW); // No XSS prevention here, users must be trusted.
        $mform->addHelpButton('video_description', 'video_description', 'videotime');

        // Add section tab plugins.
        $mform->addElement('header', 'tabs', get_string('tabs', 'videotime'));

        $mform->addElement('advcheckbox', 'enabletabs', get_string('enabletabs', 'videotime'));
        $mform->setType('enabletabs', PARAM_BOOL);
        $mform->setDefault('enabletabs', get_config('videotime', 'enabletabs'));

        foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
            if (!empty(get_config('videotimetab_' . $name, 'enabled'))) {
                $classname = "\\videotimetab_$name\\tab";
                $classname::add_form_fields($mform);
            }
        }

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add fields from extensions.
        foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
            component_callback("videotimeplugin_$name", 'add_form_fields', [$mform, get_class($this)]);
        }

        // Add standard buttons.
        $this->add_action_buttons();

        if (!videotime_has_pro()) {
            $mform->addElement('static', '', '', html_writer::link(
                new moodle_url('https://link.bdecent.de/videotimepro2'),
                html_writer::img(
                    'https://link.bdecent.de/videotimepro2/image.jpg',
                    '',
                    ['width' => '100%', 'class' => 'img-responsive', 'style' => 'max-width:700px']
                )
            ));
        }
    }

    /**
     * Add an editor for an activity's introduction field.
     *
     * NOTE: Copied from parent classes to change showdescription string.
     *
     * @param null $customlabel Override default label for editor
     * @throws coding_exception
     */
    protected function standard_intro_elements($customlabel = null) {
        global $CFG;

        $required = $CFG->requiremodintro;

        $mform = $this->_form;
        $label = is_null($customlabel) ? get_string('moduleintro') : $customlabel;

        $mform->addElement('editor', 'introeditor', $label, ['rows' => 10], [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true,
            'context' => $this->context,
            'subdirs' => true,
        ]);
        $mform->setType('introeditor', PARAM_RAW); // No XSS prevention here, users must be trusted.
        if ($required) {
            $mform->addRule('introeditor', get_string('required'), 'required', null, 'client');
        }

        // If the 'show description' feature is enabled, this checkbox appears below the intro.
        // We want to hide that when using the singleactivity course format because it is confusing.
        if ($this->_features->showdescription  && $this->courseformat->has_view_page()) {
            $mform->addElement('advcheckbox', 'showdescription', get_string('showdescription'));
            $mform->addHelpButton('showdescription', 'showdescription', 'videotime');
        }
    }

    /**
     * Add custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     * @throws coding_exception
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        if (videotime_has_pro()) {
            // Completion on view and seconds.
            $group = [];
            $group[] =& $mform->createElement(
                'advcheckbox',
                $this->get_suffixed_name('completion_on_view_time'),
                '',
                get_string('completion_on_view', 'videotime') . ':&nbsp;'
            );
            $group[] =& $mform->createElement(
                'text',
                $this->get_suffixed_name('completion_on_view_time_second'),
                '',
                ['size' => 3]
            );
            $group[] =& $mform->createElement('static', 'seconds', '', get_string('seconds', 'videotime'));
            $mform->setType($this->get_suffixed_name('completion_on_view_time_second'), PARAM_INT);
            $mform->addGroup($group, $this->get_suffixed_name('completion_on_view_time_group'), '', [' '], false);
            $mform->disabledIf(
                $this->get_suffixed_name('completion_on_view_time_second'),
                $this->get_suffixed_name('completion_on_view_time'),
                'notchecked'
            );

            $group = [];
            $group[] =& $mform->createElement(
                'advcheckbox',
                $this->get_suffixed_name('completion_on_percent'),
                '',
                get_string('completion_on_percent', 'videotime') . ':&nbsp;'
            );
            $group[] =& $mform->createElement('text', $this->get_suffixed_name('completion_on_percent_value'), '', ['size' => 3]);
            $group[] =& $mform->createElement('static', 'percent_label', '', '%');
            $mform->setType($this->get_suffixed_name('completion_on_percent_value'), PARAM_INT);
            $mform->addGroup($group, $this->get_suffixed_name('completion_on_percent_group'), '', [' '], false);
            $mform->disabledIf(
                $this->get_suffixed_name('completion_on_percent_value'),
                $this->get_suffixed_name('completion_on_percent'),
                'notchecked'
            );

            $mform->addElement(
                'advcheckbox',
                $this->get_suffixed_name('completion_on_finish'),
                '',
                get_string('completion_on_finish', 'videotime')
            );
            $mform->setType($this->get_suffixed_name('completion_on_finish'), PARAM_BOOL);

            $mform->addElement(
                'advcheckbox',
                $this->get_suffixed_name('completion_hide_detail'),
                '',
                get_string('completion_hide_detail', 'videotime')
            );
            $mform->setType($this->get_suffixed_name('completion_hide_detail'), PARAM_BOOL);

            return [
                $this->get_suffixed_name('completion_on_finish'),
                $this->get_suffixed_name('completion_hide_detail'),
                $this->get_suffixed_name('completion_on_view_time_group'),
                $this->get_suffixed_name('completion_on_percent_group'),
            ];
        }

        return [];
    }

    /**
     * Called during validation to see whether some module-specific completion rules are selected.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return (
            (!empty($data[$this->get_suffixed_name('completion_on_view_time')]) &&
            $data[$this->get_suffixed_name('completion_on_view_time_second')] != 0)) ||
            !empty($data[$this->get_suffixed_name('completion_on_finish')] ||
            (
                !empty($data[$this->get_suffixed_name('completion_on_percent')])
                && $data[$this->get_suffixed_name('completion_on_percent_value')])
            );
    }

    /**
     * Validates the form input
     *
     * @param array $data submitted data
     * @param array $files submitted files
     * @return array eventual errors indexed by the field name
     */
    public function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        if (!isset($data['vimeo_url']) || empty($data['vimeo_url'])) {
            $fs = get_file_storage();
            if (
                empty($data['livefeed']) && (
                empty($data['mediafile'])
                || !$files = $fs->get_area_files(context_user::instance($USER->id)->id, 'user', 'draft', $data['mediafile'])
                )
            ) {
                $errors['vimeo_url'] = get_string('required');
            }
        } else if (!filter_var($data['vimeo_url'], FILTER_VALIDATE_URL)) {
            $errors['vimeo_url'] = get_string('vimeo_url_invalid', 'videotime');
        }

        // Make sure seconds are set if completion on view time is enabled.
        if (
            isset($data[$this->get_suffixed_name('completion_on_view_time')])
            && $data[$this->get_suffixed_name('completion_on_view_time')]
        ) {
            if (
                isset($data[$this->get_suffixed_name('completion_on_view_time_second')])
                && !$data[$this->get_suffixed_name('completion_on_view_time_second')]
            ) {
                $errors[$this->get_suffixed_name('completion_on_view_time_second')] = get_string('required');
            }
        }

        // Make sure percent value is set if completion on percent is enabled.
        if (
            isset($data[$this->get_suffixed_name('completion_on_percent')])
            && $data[$this->get_suffixed_name('completion_on_percent')]
        ) {
            if (
                isset($data[$this->get_suffixed_name('completion_on_percent_value')]) &&
                !$data[$this->get_suffixed_name('completion_on_percent_value')]
            ) {
                $errors[$this->get_suffixed_name('completion_on_percent_value')] = get_string('required');
            }
        }

        foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
            $errors += component_callback("videotimeplugin_$name", 'validation', [$data, $files], []);
        }

        return $errors;
    }

    /**
     * Prepares the form before data are set
     *
     * @param  array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        // Editing existing instance.
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('video_description');
            $videodescription = $defaultvalues['video_description'];
            $videodescriptionformat = $defaultvalues['video_description_format'];
            $defaultvalues['video_description'] = [];
            $defaultvalues['video_description']['format'] = $videodescriptionformat;
            $defaultvalues['video_description']['text']   = file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_videotime',
                'video_description',
                0,
                [],
                $videodescription
            );
            $defaultvalues['video_description']['itemid'] = $draftitemid;

            foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
                component_callback("videotimeplugin_$name", 'data_preprocessing', [&$defaultvalues, $this->current->instance]);
            }

            foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
                $classname = "\\videotimetab_$name\\tab";
                $classname::data_preprocessing($defaultvalues, $this->current->instance);
            }
        }
    }

    /**
     * Completion suffix
     */
    public function get_suffix(): string {
        if (method_exists(parent::class, 'get_suffix')) {
            return parent::get_suffix();
        }
        return '';
    }

    /**
     * Completion condition with suffix
     *
     * @param string $name Name without suffix
     * @return string
     */
    public function get_suffixed_name($name): string {
        return $name . $this->get_suffix();
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Turn off completion settings if the checkboxes aren't ticked.
        $suffix = $this->get_suffix();
        if (!empty($data->completionunlocked)) {
            $suffix = $this->get_suffix();
            $completion = $data->{'completion' . $suffix};
            $autocompletion = !empty($completion) && $completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->{'completion_on_finish' . $suffix}) || !$autocompletion) {
                $data->{'completion_on_finish' . $suffix} = 0;
            }
            if (empty($data->{'completion_on_percent_value' . $suffix}) || !$autocompletion) {
                $data->{'completion_on_percent' . $suffix} = 0;
            }
            if (empty($data->{'completion_on_view_time_second' . $suffix}) || !$autocompletion) {
                $data->{'completion_on_view_time' . $suffix} = 0;
            }
            if (empty($data->{'completion_hide_detail' . $suffix}) || !$autocompletion) {
                $data->{'completion_hide_detail' . $suffix} = 0;
            }
        }
    }
}
