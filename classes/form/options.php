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
 * The Vimeo options form.
 *
 * @package     mod_videotime
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\form;

defined('MOODLE_INTERNAL') || die();

use core_component;
use mod_videotime\videotime_instance;
use moodleform;

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * The Vimeo options form.
 *
 * @package    mod_videotime
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class options extends moodleform {

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

        $mform->addElement('header', 'embed_options', get_string('embed_options', 'videotime'));

        // Add hidden 'disable' element used for disabling embed options when they are globally forced.
        $mform->addElement('hidden', 'disable');
        $mform->setType('disable', PARAM_INT);
        $mform->setDefault('disable', 1);

        $mform->addElement('advcheckbox', 'responsive', get_string('option_responsive', 'videotime'));
        $mform->setType('responsive', PARAM_BOOL);
        $mform->addHelpButton('responsive', 'option_responsive', 'videotime');
        $mform->setDefault('responsive', get_config('videotime', 'responsive'));
        videotime_instance::create_additional_field_form_elements('responsive', $mform);

        $mform->addElement('text', 'height', get_string('option_height', 'videotime'));
        $mform->setType('height', PARAM_INT);
        $mform->addHelpButton('height', 'option_height', 'videotime');
        $mform->disabledIf('height', 'responsive', 'checked');
        $mform->setDefault('height', get_config('videotime', 'height'));
        videotime_instance::create_additional_field_form_elements('height', $mform);

        $mform->addElement('text', 'width', get_string('option_width', 'videotime'));
        $mform->setType('width', PARAM_INT);
        $mform->addHelpButton('width', 'option_width', 'videotime');
        $mform->setDefault('width', get_config('videotime', 'width'));
        $mform->disabledIf('width', 'responsive', 'checked');
        videotime_instance::create_additional_field_form_elements('width', $mform);

        $mform->addElement('text', 'maxheight', get_string('option_maxheight', 'videotime'));
        $mform->setType('maxheight', PARAM_INT);
        $mform->addHelpButton('maxheight', 'option_maxheight', 'videotime');
        $mform->setDefault('maxheight', get_config('videotime', 'maxheight'));
        $mform->disabledIf('maxheight', 'responsive', 'checked');
        videotime_instance::create_additional_field_form_elements('maxheight', $mform);

        $mform->addElement('text', 'maxwidth', get_string('option_maxwidth', 'videotime'));
        $mform->setType('maxwidth', PARAM_INT);
        $mform->addHelpButton('maxwidth', 'option_maxwidth', 'videotime');
        $mform->setDefault('maxwidth', get_config('videotime', 'maxwidth'));
        $mform->disabledIf('maxwidth', 'responsive', 'checked');
        videotime_instance::create_additional_field_form_elements('maxwidth', $mform);

        $mform->addElement('advcheckbox', 'autoplay', get_string('option_autoplay', 'videotime'));
        $mform->setType('autoplay', PARAM_BOOL);
        $mform->addHelpButton('autoplay', 'option_autoplay', 'videotime');
        $mform->setDefault('autoplay', get_config('videotime', 'autoplay'));
        videotime_instance::create_additional_field_form_elements('autoplay', $mform);

        $mform->addElement('advcheckbox', 'byline', get_string('option_byline', 'videotime'));
        $mform->setType('byline', PARAM_BOOL);
        $mform->addHelpButton('byline', 'option_byline', 'videotime');
        $mform->setDefault('byline', get_config('videotime', 'byline'));
        videotime_instance::create_additional_field_form_elements('byline', $mform);

        $mform->addElement('text', 'color', get_string('option_color', 'videotime'));
        $mform->setType('color', PARAM_TEXT);
        $mform->addHelpButton('color', 'option_color', 'videotime');
        $mform->setDefault('color', get_config('videotime', 'color'));
        videotime_instance::create_additional_field_form_elements('color', $mform);

        $mform->addElement('advcheckbox', 'muted', get_string('option_muted', 'videotime'));
        $mform->setType('muted', PARAM_BOOL);
        $mform->addHelpButton('muted', 'option_muted', 'videotime');
        $mform->setDefault('muted', get_config('videotime', 'muted'));
        videotime_instance::create_additional_field_form_elements('muted', $mform);

        $mform->addElement('advcheckbox', 'playsinline', get_string('option_playsinline', 'videotime'));
        $mform->setType('playsinline', PARAM_BOOL);
        $mform->addHelpButton('playsinline', 'option_playsinline', 'videotime');
        $mform->setDefault('playsinline', get_config('videotime', 'playsinline'));
        videotime_instance::create_additional_field_form_elements('playsinline', $mform);

        $mform->addElement('advcheckbox', 'portrait', get_string('option_portrait', 'videotime'));
        $mform->setType('portrait', PARAM_BOOL);
        $mform->addHelpButton('portrait', 'option_portrait', 'videotime');
        $mform->setDefault('portrait', get_config('videotime', 'portrait'));
        videotime_instance::create_additional_field_form_elements('portrait', $mform);

        $mform->addElement('advcheckbox', 'speed', get_string('option_speed', 'videotime'));
        $mform->setType('speed', PARAM_BOOL);
        $mform->addHelpButton('speed', 'option_speed', 'videotime');
        $mform->setDefault('speed', get_config('videotime', 'speed'));
        videotime_instance::create_additional_field_form_elements('speed', $mform);

        $mform->addElement('advcheckbox', 'title', get_string('option_title', 'videotime'));
        $mform->setType('title', PARAM_BOOL);
        $mform->addHelpButton('title', 'option_title', 'videotime');
        $mform->setDefault('title', get_config('videotime', 'title'));
        videotime_instance::create_additional_field_form_elements('title', $mform);

        $mform->addElement('advcheckbox', 'transparent', get_string('option_transparent', 'videotime'));
        $mform->setType('transparent', PARAM_BOOL);
        $mform->addHelpButton('transparent', 'option_transparent', 'videotime');
        $mform->setDefault('transparent', get_config('videotime', 'transparent'));
        videotime_instance::create_additional_field_form_elements('transparent', $mform);

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
}
