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
            $group = [];
            $group[] =& $mform->createElement('checkbox', 'completion_on_view_time', '', get_string('completion_on_view', 'videotime') . ':&nbsp;');
            $group[] =& $mform->createElement('text', 'completion_on_view_time_second', '', ['size' => 3]);
            $group[] =& $mform->createElement('static', 'seconds', '', get_string('seconds', 'videotime'));
            $mform->setType('completion_on_view_time_second', PARAM_INT);
            $mform->addGroup($group, 'completion_on_view', '', array(' '), false);
            $mform->disabledIf('completion_on_view_time_second', 'completion_on_view_time', 'notchecked');

            $mform->addElement('checkbox', 'completion_on_finish', '', get_string('completion_on_finish', 'videotime'));
            $mform->setType('completion_on_finish', PARAM_BOOL);

            return ['completion_on_view', 'completion_on_finish'];
        }

        return [];
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completion_on_view_time']) && $data['completion_on_view_time_second']!=0) ||
            !empty($data['completion_on_finish']);
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

        if (isset($data['completion_on_view_time']) && $data['completion_on_view_time']) {
            if (isset($data['completion_on_view_time_second']) && !$data['completion_on_view_time_second']) {
                $errors['completion_on_view_time_second'] = get_string('required');
            }
        }

        return $errors;
    }

    public function data_preprocessing(&$default_values)
    {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('video_description');
            $video_description = $default_values['video_description'];
            $video_description_format = $default_values['video_description_format'];
            $default_values['video_description'] = [];
            $default_values['video_description']['format'] = $video_description_format;
            $default_values['video_description']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_videotime', 'video_description', 0, [], $video_description);
            $default_values['video_description']['itemid'] = $draftitemid;
        }
    }
}
