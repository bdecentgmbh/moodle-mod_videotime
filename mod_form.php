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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

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
            $mform->addElement('static', '', '', html_writer::link(new moodle_url('https://link.bdecent.de/videotimepro1'),
                html_writer::img('https://link.bdecent.de/videotimepro1/image.jpg', '',
                    ['width' => '100%', 'class' => 'img-responsive', 'style' => 'max-width:700px'])));
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
            $mform->addGroup($group, '', get_string('vimeo_url', 'videotime'));

            $group = [];
            if (!$needssetup) {
                $group[] = $mform->createElement('static', 'choose_video_label', '', '- or -');
                $group[] = $mform->createElement('button', 'choose_video', get_string('choose_video', 'videotime'));
            } else if (is_siteadmin()) {
                $group[] = $mform->createElement('static', 'choose_video_label', '', '- or -');
                $group[] = $mform->createElement('html',
                    html_writer::link(new moodle_url('/mod/videotime/plugin/repository/overview.php'),
                        get_string('setup_repository', 'videotime')));
            }
            $mform->addGroup($group);

            $PAGE->requires->js_call_amd('videotimeplugin_repository/mod_form', 'init',
                [videotime_is_totara(), $this->context->id]);
        } else {
            $mform->addElement('text', 'vimeo_url', get_string('vimeo_url', 'videotime'), ['size' => 100]);
            $mform->addHelpButton('vimeo_url', 'vimeo_url', 'videotime');
        }

        $mform->setType('vimeo_url', PARAM_URL);

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('activity_name', 'mod_videotime'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'activity_name', 'mod_videotime');

        if (videotime_has_pro()) {

            if (videotime_has_repository()) {
                $group = [];
                $group[] = $mform->createElement('advcheckbox', 'show_title', '', get_string('show_title', 'videotime'));
                $mform->setDefault('show_title', 1);
                if (method_exists($mform, 'hideIf')) {
                    $mform->hideIf('show_title', 'label_mode', 'noeq', 2);
                } else {
                    $mform->disabledIf('show_title', 'label_mode', 'noeq', 2);
                }
                $mform->setDefault('show_title', get_config('videotime', 'show_title'));
                videotime_instance::create_additional_field_form_elements('show_title', $mform, $group);

                $group[] = $mform->createElement('advcheckbox', 'show_description', '',
                    get_string('show_description', 'videotime'));
                $mform->setDefault('show_description', 1);
                $group[] = $mform->createElement('advcheckbox', 'show_description_in_player', '',
                    get_string('show_description_in_player', 'videotime'));
                $mform->setDefault('show_description_in_player', 1);
                if (method_exists($mform, 'hideIf')) {
                    $mform->hideIf('show_description', 'label_mode', 'noeq', 2);
                    $mform->hideIf('show_description_in_player', 'label_mode', 'eq', 2);
                } else {
                    $mform->disabledIf('show_description', 'label_mode', 'noeq', 2);
                    $mform->disabledIf('show_description_in_player', 'label_mode', 'eq', 2);
                }
                $mform->setDefault('show_description', get_config('videotime', 'show_description'));
                videotime_instance::create_additional_field_form_elements('show_description', $mform, $group);

                $group[] = $mform->createElement('advcheckbox', 'show_tags', '', get_string('show_tags', 'videotime'));
                $mform->setDefault('show_tags', 1);
                if (method_exists($mform, 'hideIf')) {
                    $mform->hideIf('show_tags', 'label_mode', 'noeq', 2);
                } else {
                    $mform->disabledIf('show_tags', 'label_mode', 'noeq', 2);
                }
                $mform->setDefault('show_tags', get_config('videotime', 'show_tags'));
                videotime_instance::create_additional_field_form_elements('show_tags', $mform, $group);

                $group[] = $mform->createElement('advcheckbox', 'show_duration', '', get_string('show_duration', 'videotime'));
                $mform->setDefault('show_duration', 1);
                if (method_exists($mform, 'hideIf')) {
                    $mform->hideIf('show_duration', 'label_mode', 'noeq', 2);
                } else {
                    $mform->disabledIf('show_duration', 'label_mode', 'noeq', 2);
                }
                $mform->setDefault('show_duration', get_config('videotime', 'show_duration'));
                videotime_instance::create_additional_field_form_elements('show_duration', $mform, $group);

                $group[] = $mform->createElement('advcheckbox', 'show_viewed_duration', '',
                    get_string('show_viewed_duration', 'videotime'));
                $mform->setDefault('show_viewed_duration', 1);
                if (method_exists($mform, 'hideIf')) {
                    $mform->hideIf('show_viewed_duration', 'label_mode', 'noeq', 2);
                } else {
                    $mform->disabledIf('show_viewed_duration', 'label_mode', 'noeq', 2);
                }
                $mform->setDefault('show_viewed_duration', get_config('videotime', 'show_viewed_duration'));
                videotime_instance::create_additional_field_form_elements('show_viewed_duration', $mform, $group);

                $mform->addGroup($group, 'displaygroup', get_string('display_options', 'videotime'), array('<br>'), false);

                $mform->addElement('select', 'columns', get_string('columns', 'videotime'), [
                    1 => '1 (100% width)',
                    2 => '2 (50% width)',
                    3 => '3 (33% width)',
                    4 => '4 (25% width'
                ]);
                $mform->setType('columns', PARAM_INT);
                $mform->addHelpButton('columns', 'columns', 'videotime');
                if (method_exists($mform, 'hideIf')) {
                    $mform->hideIf('columns', 'label_mode', 'noeq', 2);
                } else {
                    $mform->disabledIf('columns', 'label_mode', 'noeq', 2);
                }
                $mform->setDefault('columns', get_config('videotime', 'columns'));
                if (get_config('videotime', 'columns_force')) {
                    if (method_exists($mform, 'hideIf')) {
                        $mform->hideIf('columns_forced', 'label_mode', 'noeq', 2);
                    } else {
                        $mform->disabledIf('columns_forced', 'label_mode', 'noeq', 2);
                    }
                    $mform->disabledIf('columns', 'disable', 'eq', 1);
                }

                $mform->addElement('select', 'preview_picture', get_string('preview_picture', 'videotime'), [
                    \videotimeplugin_repository\video_interface::PREVIEW_PICTURE_BIG => '1920 x 1200',
                    \videotimeplugin_repository\video_interface::PREVIEW_PICTURE_MEDIUM => '640 x 400',
                    \videotimeplugin_repository\video_interface::PREVIEW_PICTURE_BIG_WITH_PLAY => '1920 x 1200 ' .
                        get_string('with_play_button', 'videotime'),
                    \videotimeplugin_repository\video_interface::PREVIEW_PICTURE_MEDIUM_WITH_PLAY => '640 x 400 ' .
                        get_string('with_play_button', 'videotime')
                ]);
                $mform->setType('preview_picture', PARAM_INT);
                if (method_exists($mform, 'hideIf')) {
                    $mform->hideIf('preview_picture', 'label_mode', 'noeq', 2);
                } else {
                    $mform->disabledIf('preview_picture', 'label_mode', 'noeq', 2);
                }
                $mform->setDefault('preview_picture', get_config('videotime', 'preview_picture'));
                if (get_config('videotime', 'preview_picture_force')) {
                    if (method_exists($mform, 'hideIf')) {
                        $mform->hideIf('preview_picture_forced', 'label_mode', 'noeq', 2);
                    } else {
                        $mform->disabledIf('preview_picture_forced', 'label_mode', 'noeq', 2);
                    }
                    $mform->disabledIf('preview_picture', 'disable', 'eq', 1);
                }
            }
        }

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Video Time video description.
        $mform->addElement('editor', 'video_description', get_string('video_description', 'videotime'),
            array('rows' => 10), array('maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true, 'context' => $this->context, 'subdirs' => true));
        $mform->setType('video_description', PARAM_RAW); // No XSS prevention here, users must be trusted.
        $mform->addHelpButton('video_description', 'video_description', 'videotime');

        // Add section tab plugins.
        $mform->addElement('header', 'tabs', get_string('tabs', 'videotime'));

        $mform->addElement('advcheckbox', 'enabletabs', get_string('enabletabs', 'videotime'));
        $mform->setType('enabletabs', PARAM_BOOL);

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
            $mform->addElement('static', '', '', html_writer::link(new moodle_url('https://link.bdecent.de/videotimepro2'),
                html_writer::img('https://link.bdecent.de/videotimepro2/image.jpg', '',
                    ['width' => '100%', 'class' => 'img-responsive', 'style' => 'max-width:700px'])));
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
    protected function standard_intro_elements($customlabel=null) {
        global $CFG;

        $required = $CFG->requiremodintro;

        $mform = $this->_form;
        $label = is_null($customlabel) ? get_string('moduleintro') : $customlabel;

        $mform->addElement('editor', 'introeditor', $label, array('rows' => 10), array('maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true, 'context' => $this->context, 'subdirs' => true));
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
            $group[] =& $mform->createElement('advcheckbox', 'completion_on_view_time', '',
                get_string('completion_on_view', 'videotime') . ':&nbsp;');
            $group[] =& $mform->createElement('text', 'completion_on_view_time_second', '', ['size' => 3]);
            $group[] =& $mform->createElement('static', 'seconds', '', get_string('seconds', 'videotime'));
            $mform->setType('completion_on_view_time_second', PARAM_INT);
            $mform->addGroup($group, 'completion_on_view', '', array(' '), false);
            $mform->disabledIf('completion_on_view_time_second', 'completion_on_view_time', 'notchecked');

            $group = [];
            $group[] =& $mform->createElement('advcheckbox', 'completion_on_percent', '',
                get_string('completion_on_percent', 'videotime') . ':&nbsp;');
            $group[] =& $mform->createElement('text', 'completion_on_percent_value', '', ['size' => 3]);
            $group[] =& $mform->createElement('static', 'percent_label', '', '%');
            $mform->setType('completion_on_percent_value', PARAM_INT);
            $mform->addGroup($group, 'completion_on_percent', '', array(' '), false);
            $mform->disabledIf('completion_on_percent_value', 'completion_on_percent', 'notchecked');

            $mform->addElement('advcheckbox', 'completion_on_finish', '', get_string('completion_on_finish', 'videotime'));
            $mform->setType('completion_on_finish', PARAM_BOOL);

            $mform->addElement('advcheckbox', 'completion_hide_detail', '', get_string('completion_hide_detail', 'videotime'));
            $mform->setType('completion_hide_detail', PARAM_BOOL);

            return ['completion_on_view', 'completion_on_percent', 'completion_on_finish', 'completion_hide_detail'];
        } else {
            // Remove completion on grade since grade settings are not displayed for free version.
            $mform->removeElement('completionusegrade');
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
            (!empty($data['completion_on_view_time']) && $data['completion_on_view_time_second'] != 0)) ||
            !empty($data['completion_on_finish'] ||
            (!empty($data['completion_on_percent']) && $data['completion_on_percent_value']));
    }

    /**
     * Validates the form input
     *
     * @param array $data submitted data
     * @param array $files submitted files
     * @return array eventual errors indexed by the field name
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!isset($data['vimeo_url']) || empty($data['vimeo_url'])) {
            $errors['vimeo_url'] = get_string('required');
        } else if (!filter_var($data['vimeo_url'], FILTER_VALIDATE_URL)) {
            $errors['vimeo_url'] = get_string('vimeo_url_invalid', 'videotime');
        }

        // Make sure seconds are set if completion on view time is enabled.
        if (isset($data['completion_on_view_time']) && $data['completion_on_view_time']) {
            if (isset($data['completion_on_view_time_second']) && !$data['completion_on_view_time_second']) {
                $errors['completion_on_view_time_second'] = get_string('required');
            }
        }

        // Make sure percent value is set if completion on percent is enabled.
        if (isset($data['completion_on_percent']) && $data['completion_on_percent']) {
            if (isset($data['completion_on_percent_value']) && !$data['completion_on_percent_value']) {
                $errors['completion_on_percent_value'] = get_string('required');
            }
        }

        return $errors;
    }

    /**
     * Prepares the form before data are set
     *
     * @param  array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        // Editing existing instance.
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('video_description');
            $videodescription = $defaultvalues['video_description'];
            $videodescriptionformat = $defaultvalues['video_description_format'];
            $defaultvalues['video_description'] = [];
            $defaultvalues['video_description']['format'] = $videodescriptionformat;
            $defaultvalues['video_description']['text']   = file_prepare_draft_area($draftitemid, $this->context->id,
                'mod_videotime', 'video_description', 0, [], $videodescription);
            $defaultvalues['video_description']['itemid'] = $draftitemid;

            foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
                $classname = "\\videotimetab_$name\\tab";
                $classname::data_preprocessing($defaultvalues, $this->current->instance);
            }
        }
    }
}
