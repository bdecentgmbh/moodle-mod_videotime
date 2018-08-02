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
 * The main mod_vimeo configuration form.
 *
 * @package     mod_vimeo
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_vimeo
 * @copyright  2018 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_vimeo_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('activity_name', 'mod_vimeo'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'activity_name', 'mod_vimeo');

        $mform->addElement('text', 'vimeo_url', get_string('vimeo_url', 'vimeo'), ['size' => 100]);
        $mform->setType('vimeo_url', PARAM_URL);
        $mform->addRule('vimeo_url', get_string('required'), 'required');
        $mform->addHelpButton('vimeo_url', 'vimeo_url', 'vimeo');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Vimeo video description
        $mform->addElement('editor', 'video_description', get_string('video_description', 'vimeo'), array('rows' => 10), array('maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true, 'context' => $this->context, 'subdirs' => true));
        $mform->setType('video_description', PARAM_RAW); // no XSS prevention here, users must be trusted

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    public function validation($data, $files)
    {
        $errors = [];
        if (!filter_var($data['vimeo_url'], FILTER_VALIDATE_URL)) {
            $errors['vimeo_url'] = get_string('vimeo_url_invalid', 'vimeo');
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
            $default_values['video_description']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_vimeo', 'video_description', 0, [], $video_description);
            $default_values['video_description']['itemid'] = $draftitemid;
        }
    }
}
