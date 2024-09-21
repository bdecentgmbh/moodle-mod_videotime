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
 * @package     videotimeplugin_live
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimeplugin_live\form;

defined('MOODLE_INTERNAL') || die();

use core_component;
use html_writer;
use mod_videotime\videotime_instance;
use moodleform;
use moodle_url;

require_once("$CFG->libdir/formslib.php");

/**
 * The Vimeo options form.
 *
 * @package    videotimeplugin_live
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class options extends moodleform {
    /**
     * @var array Vimeo embed option fields.
     */
    private static $optionfields = [
        'autoplay',
        'byline',
        'height',
        'muted',
        'playsinline',
        'portrait',
        'responsive',
        'speed',
        'title',
        'autopause',
        'background',
        'controls',
        'pip',
        'dnt',
        'width',
    ];

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
        }

        $mform->addElement('header', 'embed_options', get_string('embed_options', 'videotime'));

        // Add hidden 'disable' element used for disabling embed options when they are globally forced.
        $mform->addElement('hidden', 'disable');
        $mform->setType('disable', PARAM_INT);
        $mform->setDefault('disable', 1);

        $mform->addElement('hidden', 'livefeed');
        $mform->setType('livefeed', PARAM_BOOL);
        $mform->setDefault('livefeed', true);

        $mform->addElement('advcheckbox', 'responsive', get_string('option_responsive', 'videotime'));
        $mform->setType('responsive', PARAM_BOOL);
        $mform->addHelpButton('responsive', 'option_responsive', 'videotime');
        $mform->setDefault('responsive', get_config('videotime', 'responsive'));
        self::create_additional_field_form_elements('responsive', $mform);

        $mform->addElement('text', 'height', get_string('option_height', 'videotime'));
        $mform->setType('height', PARAM_INT);
        $mform->addHelpButton('height', 'option_height', 'videotime');
        $mform->disabledIf('height', 'responsive', 'checked');
        $mform->setDefault('height', get_config('videotime', 'height'));
        self::create_additional_field_form_elements('height', $mform);

        $mform->addElement('text', 'width', get_string('option_width', 'videotime'));
        $mform->setType('width', PARAM_INT);
        $mform->addHelpButton('width', 'option_width', 'videotime');
        $mform->setDefault('width', get_config('videotime', 'width'));
        $mform->disabledIf('width', 'responsive', 'checked');
        self::create_additional_field_form_elements('width', $mform);

        $mform->addElement('advcheckbox', 'controls', get_string('option_controls', 'videotime'));
        $mform->setType('controls', PARAM_BOOL);
        $mform->addHelpButton('controls', 'option_controls', 'videotime');
        $mform->setDefault('controls', get_config('videotime', 'controls'));
        self::create_additional_field_form_elements('controls', $mform);

        $mform->addElement('advcheckbox', 'muted', get_string('option_muted', 'videotime'));
        $mform->setType('muted', PARAM_BOOL);
        $mform->addHelpButton('muted', 'option_muted', 'videotime');
        $mform->setDefault('muted', get_config('videotime', 'muted'));
        self::create_additional_field_form_elements('muted', $mform);

        $mform->addElement('advcheckbox', 'playsinline', get_string('option_playsinline', 'videotime'));
        $mform->setType('playsinline', PARAM_BOOL);
        $mform->addHelpButton('playsinline', 'option_playsinline', 'videotime');
        $mform->setDefault('playsinline', get_config('videotime', 'playsinline'));
        self::create_additional_field_form_elements('playsinline', $mform);

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
     * Allow additional form elements to be added for each Video Time field.
     *
     * @param string $fieldname
     * @param \MoodleQuickForm $mform
     * @param array $group
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function create_additional_field_form_elements(string $fieldname, \MoodleQuickForm $mform, &$group = null) {
        $advanced = explode(',', get_config('videotimeplugin_live', 'advanced'));
        $forced = explode(',', get_config('videotimeplugin_live', 'forced'));

        if (in_array($fieldname, $advanced)) {
            $mform->setAdvanced($fieldname);
        }

        if (in_array($fieldname, $forced)) {
            if (in_array($fieldname, self::$optionfields)) {
                $label = get_string('option_' . $fieldname, 'videotime');
            } else {
                $label = get_string($fieldname, 'videotime');
            }

            $value = get_config('videotimeplugin_live', $fieldname);
            if ($group) {
                $element = null;
                foreach ($group as $element) {
                    if ($element->getName() == $fieldname) {
                        break;
                    }
                }
            } else {
                $element = $group ? null : $mform->getElement($fieldname);
            }

            if ($element) {
                if ($element instanceof \MoodleQuickForm_checkbox || $element instanceof \MoodleQuickForm_advcheckbox) {
                    $value = $value ? get_string('yes') : get_string('no');
                }
            } else if ($element instanceof \MoodleQuickForm_radio) {
                if ($element->getValue() == $value) {
                    $value = $element->getLabel();
                }
            }

            $element = $mform->createElement('static', $fieldname . '_forced', '', get_string('option_forced', 'videotime', [
                'option' => $label,
                'value' => $value,
            ]));
            if ($group) {
                $group[] = $element;
            } else {
                $mform->addElement($element);
                $mform->removeElement($fieldname);
            }
            $mform->disabledIf($fieldname, 'disable', 'eq', 1);
        }
    }
}
