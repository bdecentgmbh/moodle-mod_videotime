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
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_videotime
 * @copyright  2018 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videotime_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

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

        $mform->addElement('text', 'vimeo_url', get_string('vimeo_url', 'videotime'), ['size' => 100]);
        $mform->setType('vimeo_url', PARAM_URL);
        $mform->addRule('vimeo_url', get_string('required'), 'required');
        $mform->addHelpButton('vimeo_url', 'vimeo_url', 'videotime');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Video Time video description
        $mform->addElement('editor', 'video_description', get_string('video_description', 'videotime'), array('rows' => 10), array('maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true, 'context' => $this->context, 'subdirs' => true));
        $mform->setType('video_description', PARAM_RAW); // no XSS prevention here, users must be trusted
        $mform->addHelpButton('video_description', 'video_description', 'videotime');

        // Preview image
        // Don't display for now.
//        $mform->addElement('filemanager', 'preview_image', get_string('preview_image', 'videotime'), null, [
//            'maxfiles' => 1,
//            'accepted_types' => ['png', 'jpg', 'jpeg']
//        ]);
//        $mform->addHelpButton('preview_image', 'preview_image', 'videotime');

        $mform->addElement('header', 'embed_options', get_string('embed_options', 'videotime'));

        $mform->addElement('advcheckbox', 'autoplay', get_string('option_autoplay', 'videotime'));
        $mform->setType('autoplay', PARAM_BOOL);
        $mform->addHelpButton('autoplay', 'option_autoplay', 'videotime');
        $mform->setDefault('autoplay', 0);

        $mform->addElement('advcheckbox', 'byline', get_string('option_byline', 'videotime'));
        $mform->setType('byline', PARAM_BOOL);
        $mform->addHelpButton('byline', 'option_byline', 'videotime');
        $mform->setDefault('byline', 1);

        $mform->addElement('text', 'color', get_string('option_color', 'videotime'));
        $mform->setType('color', PARAM_TEXT);
        $mform->addHelpButton('color', 'option_color', 'videotime');
        $mform->setDefault('color', '00adef');

        $mform->addElement('text', 'height', get_string('option_height', 'videotime'));
        $mform->setType('height', PARAM_INT);
        $mform->addHelpButton('height', 'option_height', 'videotime');
        $mform->setDefault('height', null);

        $mform->addElement('text', 'width', get_string('option_width', 'videotime'));
        $mform->setType('width', PARAM_INT);
        $mform->addHelpButton('width', 'option_width', 'videotime');
        $mform->setDefault('width', null);

        $mform->addElement('text', 'maxheight', get_string('option_maxheight', 'videotime'));
        $mform->setType('maxheight', PARAM_INT);
        $mform->addHelpButton('maxheight', 'option_maxheight', 'videotime');
        $mform->setDefault('maxheight', null);

        $mform->addElement('text', 'maxwidth', get_string('option_maxwidth', 'videotime'));
        $mform->setType('maxwidth', PARAM_INT);
        $mform->addHelpButton('maxwidth', 'option_maxwidth', 'videotime');
        $mform->setDefault('maxwidth', null);

        $mform->addElement('advcheckbox', 'muted', get_string('option_muted', 'videotime'));
        $mform->setType('muted', PARAM_BOOL);
        $mform->addHelpButton('muted', 'option_muted', 'videotime');
        $mform->setDefault('muted', 0);

        $mform->addElement('advcheckbox', 'playsinline', get_string('option_playsinline', 'videotime'));
        $mform->setType('playsinline', PARAM_BOOL);
        $mform->addHelpButton('playsinline', 'option_playsinline', 'videotime');
        $mform->setDefault('playsinline', 1);

        $mform->addElement('advcheckbox', 'portrait', get_string('option_portrait', 'videotime'));
        $mform->setType('portrait', PARAM_BOOL);
        $mform->addHelpButton('portrait', 'option_portrait', 'videotime');
        $mform->setDefault('portrait', 1);

        $mform->addElement('advcheckbox', 'speed', get_string('option_speed', 'videotime'));
        $mform->setType('speed', PARAM_BOOL);
        $mform->addHelpButton('speed', 'option_speed', 'videotime');
        $mform->setDefault('speed', 0);

        $mform->addElement('advcheckbox', 'title', get_string('option_title', 'videotime'));
        $mform->setType('title', PARAM_BOOL);
        $mform->addHelpButton('title', 'option_title', 'videotime');
        $mform->setDefault('title', 1);

        $mform->addElement('advcheckbox', 'transparent', get_string('option_transparent', 'videotime'));
        $mform->setType('transparent', PARAM_BOOL);
        $mform->addHelpButton('transparent', 'option_transparent', 'videotime');
        $mform->setDefault('transparent', 1);

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
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
        $mform->setType('introeditor', PARAM_RAW); // no XSS prevention here, users must be trusted
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
            $group[] =& $mform->createElement('checkbox', 'completion_on_view_time', '', get_string('completion_on_view', 'videotime') . ':&nbsp;');
            $group[] =& $mform->createElement('text', 'completion_on_view_time_second', '', ['size' => 3]);
            $group[] =& $mform->createElement('static', 'seconds', '', get_string('seconds', 'videotime'));
            $mform->setType('completion_on_view_time_second', PARAM_INT);
            $mform->addGroup($group, 'completion_on_view', '', array(' '), false);
            $mform->disabledIf('completion_on_view_time_second', 'completion_on_view_time', 'notchecked');

            $group = [];
            $group[] =& $mform->createElement('checkbox', 'completion_on_percent', '', get_string('completion_on_percent', 'videotime') . ':&nbsp;');
            $group[] =& $mform->createElement('text', 'completion_on_percent_value', '', ['size' => 3]);
            $group[] =& $mform->createElement('static', 'percent_label', '', '%');
            $mform->setType('completion_on_percent_value', PARAM_INT);
            $mform->addGroup($group, 'completion_on_percent', '', array(' '), false);
            $mform->disabledIf('completion_on_percent_value', 'completion_on_percent', 'notchecked');

            $mform->addElement('checkbox', 'completion_on_finish', '', get_string('completion_on_finish', 'videotime'));
            $mform->setType('completion_on_finish', PARAM_BOOL);

            return ['completion_on_view', 'completion_on_percent', 'completion_on_finish'];
        }

        return [];
    }

    function completion_rule_enabled($data) {
        return (
            (!empty($data['completion_on_view_time']) && $data['completion_on_view_time_second']!=0)) ||
            !empty($data['completion_on_finish'] ||
            (!empty($data['completion_on_percent']) && $data['completion_on_percent_value']));
    }

    /**
     * @param $data
     * @param $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files)
    {
        $errors = [];
        if (!filter_var($data['vimeo_url'], FILTER_VALIDATE_URL)) {
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

    public function data_preprocessing(&$default_values)
    {
        // Editing existing instance.
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('video_description');
            $video_description = $default_values['video_description'];
            $video_description_format = $default_values['video_description_format'];
            $default_values['video_description'] = [];
            $default_values['video_description']['format'] = $video_description_format;
            $default_values['video_description']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_videotime', 'video_description', 0, [], $video_description);
            $default_values['video_description']['itemid'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('preview_image');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_videotime', 'preview_image', 0,
                array('subdirs' => 0, 'maxfiles' => 1));
            $default_values['preview_image'] = $draftitemid;
        }
    }
}
