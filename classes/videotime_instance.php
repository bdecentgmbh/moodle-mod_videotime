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
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a single Video Time activity module. Adds more functionality when working with instances.
 *
 * @package mod_videotime
 */
class videotime_instance {

    const NORMAL_MODE = 0;
    const LABEL_MODE = 1;
    const PREVIEW_MODE = 2;

    /**
     * @var \stdClass
     */
    private $record;

    /**
     * @var array Temporary storage for force settings. Do not use directly. use get_force_settings() instead.
     */
    private $forcesettings;

    /**
     * @var array Vimeo embed option fields.
     */
    private static $optionfields = [
        'autoplay',
        'byline',
        'color',
        'height',
        'maxheight',
        'maxwidth',
        'muted',
        'playsinline',
        'portrait',
        'responsive',
        'speed',
        'title',
        'transparent',
        'width',
    ];

    /**
     * Get a new object by Video Time instance ID.
     *
     * @param int $id
     * @return videotime_instance
     * @throws \dml_exception
     */
    public static function instance_by_id($id) : videotime_instance {
        global $DB;

        return new videotime_instance($DB->get_record('videotime', ['id' => $id], '*', MUST_EXIST));
    }

    /**
     * @param \stdClass $instancerecord
     */
    protected function __construct(\stdClass $instancerecord)
    {
        $this->record = $instancerecord;
    }

    /**
     * Get field of instance record. Provide forced values if forced setting is enabled.
     *
     * @param string $name
     * @return mixed|null
     * @throws \dml_exception
     */
    public function __get($name)
    {
        if (isset($this->record->$name)) {
            if ($this->is_field_forced($name)) {
                return $this->get_forced_value($name);
            }

            return $this->record->$name;
        }

        return null;
    }

    /**
     * This is for backwards compatibility. Some code may still treat the instance as a stdClass.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->record->$name = $value;
    }

    /**
     * Get force settings. These settings will override fields on the Video Time instance.
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_force_settings() : array {
        if (is_null($this->forcesettings)) {
            $this->forcesettings = [];
            foreach (get_config('videotime') as $name => $value) {
                if (substr($name, -strlen('_force')) === '_force') {
                    $this->forcesettings[$name] = $value;
                }
            }
        }

        return $this->forcesettings;
    }

    /**
     * Check if setting has a forced value.
     *
     * @param string $fieldname
     * @return bool
     * @throws \dml_exception
     */
    public function is_field_forced($fieldname) : bool {
        return isset($this->get_force_settings()[$fieldname . '_force'])
            && $this->get_force_settings()[$fieldname . '_force'];
    }

    /**
     * @param $fieldname
     * @return mixed
     * @throws \dml_exception
     */
    public function get_forced_value($fieldname) {
        return get_config('videotime', $fieldname);
    }

    /**
     * Get database record for Video Time instance
     *
     * @param bool $useforcedsettings
     * @return \stdClass
     * @throws \dml_exception
     */
    public function to_record($useforcedsettings = true) {
        $record = clone $this->record;

        if ($useforcedsettings) {
            foreach ($this->get_force_settings() as $name => $enabled) {
                $fieldname = str_replace('_force', '', $name);
                if (isset($record->$fieldname)) {
                    // If option is globally forced, use the default instead.
                    if ($this->is_field_forced($fieldname)) {
                        $record->$fieldname = get_config('videotime', $fieldname);
                    }
                }
            }
        }

        return $record;
    }

    /**
     * Allow additional form elements to be added for each Video Time field.
     *
     * @param string $fieldname
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function create_additional_field_form_elements(string $fieldname, \MoodleQuickForm $mform, &$group = null) {
        if (get_config('videotime', $fieldname . '_force')) {

            if (in_array($fieldname, self::$optionfields)) {
                $label = get_string('option_' . $fieldname, 'videotime');
            } else {
                $label = get_string($fieldname, 'videotime');
            }

            $value = get_config('videotime', $fieldname);
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
                'value' => $value
            ]));
            if ($group) {
                $group[] = $element;
            } else {
                $mform->addElement($element);
            }
            $mform->disabledIf($fieldname, 'disable', 'eq', 1);
        }
    }

    /**
     * Get display mode options.
     *
     * @return array
     * @throws \coding_exception
     */
    public static function get_mode_options() {
        return [
            self::NORMAL_MODE => get_string('normal_mode', 'videotime'),
            self::LABEL_MODE => get_string('label_mode', 'videotime'),
            self::PREVIEW_MODE => get_string('preview_mode', 'videotime')
        ];
    }
}