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
 * Represents a single Video Time activity module. Adds more functionality when working with instances.
 *
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime;

use external_description;
use mod_videotime\local\tabs\tabs;
use mod_videotime\output\next_activity_button;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->dirroot/mod/videotime/lib.php");

/**
 * Represents a single Video Time activity module. Adds more functionality when working with instances.
 *
 * @package mod_videotime
 */
class videotime_instance implements \renderable, \templatable {

    /** const int */
    const NORMAL_MODE = 0;

    /** const int */
    const LABEL_MODE = 1;

    /** const int */
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
     * Temporary storage for course module. Use $this->get_cm() instead.
     *
     * @var \stdClass|null
     */
    private $cm = null;

    /**
     * Temporary storage for next activity button. Use $this->get_next_activity_button() instead.
     *
     * @var next_activity_button
     */
    private $nextactivitybutton = null;

    /**
     * @var bool
     */
    private $embed = false;

    /**
     * @var tabs
     */
    private $tabs = null;

    /**
     * @var string Random, unique element ID.
     */
    private $uniqueid;

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
        'autopause',
        'background',
        'controls',
        'pip',
        'dnt',
        'width',
        'preventfastforwarding'
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

        $instance = new videotime_instance($DB->get_record('videotime', ['id' => $id], '*', MUST_EXIST));
        if ($instance->enabletabs) {
            $instance->tabs = new tabs($instance);
        }
        return $instance;
    }

    /**
     * Constructor
     *
     * @param \stdClass $instancerecord
     */
    protected function __construct(\stdClass $instancerecord) {
        $this->uniqueid = uniqid();
        $this->record = $instancerecord;
    }

    /**
     * Get field of instance record. Provide forced values if forced setting is enabled.
     *
     * @param string $name
     * @return mixed|null
     * @throws \dml_exception
     */
    public function __get($name) {
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
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
        $this->record->$name = $value;
    }

    /**
     * Get context of this Video Time instance.
     *
     * @return \context
     * @throws \coding_exception
     */
    public function get_context(): \context {
        return \context_module::instance($this->get_cm()->id);
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
     * Get forced value of field from config
     *
     * @param string $fieldname
     * @return mixed
     * @throws \dml_exception
     */
    public function get_forced_value($fieldname) {
        return get_config('videotime', $fieldname);
    }

    /**
     * Set if this instance will be used as an embed during rendering.
     *
     * @param bool $embed
     */
    public function set_embed(bool $embed): void {
        $this->embed = $embed;
    }

    /**
     * Whether instance is embedded
     *
     * @return bool
     */
    public function is_embed(): bool {
        return $this->embed;
    }

    /**
     * Get course module of this instance.
     *
     * @return \stdClass
     * @throws \coding_exception
     */
    public function get_cm() {
        if (is_null($this->cm)) {
            $this->cm = get_coursemodule_from_instance('videotime', $this->id);
        }
        return $this->cm;
    }

    /**
     * Get unique element ID.
     *
     * @return string
     */
    public function get_uniqueid(): string {
        return $this->uniqueid;
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

        $record->name = format_string($record->name, FORMAT_HTML);

        if (
            !empty($record->show_description_in_player)
            && !class_exists('core\\output\\activity_header')
        ) {
            $record->intro  = file_rewrite_pluginfile_urls($record->intro, 'pluginfile.php', $this->get_context()->id,
                'mod_videotime', 'intro', null);
            $record->intro = format_text($record->intro, $record->introformat, [
                'noclean' => true,
            ]);
        } else {
            $record->intro = '';
        }

        $record->video_description = file_rewrite_pluginfile_urls($record->video_description, 'pluginfile.php',
            $this->get_context()->id, 'mod_videotime', 'video_description', 0);
        $record->video_description = format_text($record->video_description, $record->video_description_format, [
            'noclean' => true,
        ]);

        $record->intro_excerpt = videotime_get_excerpt($record->intro);
        $record->show_more_link = strlen(strip_tags($record->intro_excerpt)) < strlen(strip_tags($record->intro));

        return $record;
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

    /**
     * Get next activity button for instance.
     *
     * @return next_activity_button|null
     * @throws \coding_exception
     */
    public function get_next_activity_button() {
        // Next activity button is a pro feature.
        if (videotime_has_pro() && is_null($this->nextactivitybutton)) {
            $this->nextactivitybutton = new next_activity_button(\cm_info::create($this->get_cm()));
        }

        return $this->nextactivitybutton;
    }

    /**
     * Get the current time the user has watched or paused at. Used for resuming playback.
     *
     * @param int $userid
     * @return float
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_resume_time($userid): float {
        // Resuming is a pro feature.
        if (!videotime_has_pro()) {
            return 0;
        }

        if (!$sessions = \videotimeplugin_pro\module_sessions::get($this->get_cm()->id, $userid)) {
            return 0;
        }

        if ($this->resume_playback) {
            return $sessions->get_current_watch_time();
        }

        return 0;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return \stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $PAGE;
        $cm = get_coursemodule_from_instance('videotime', $this->id);

        $context = [
            'instance' => $this->to_record(),
            'cmid' => $cm->id,
            'haspro' => videotime_has_pro(),
            'interval' => 5,
            'plugins' => file_exists($CFG->dirroot . '/mod/videotime/plugin/pro/templates/plugins.mustache'),
            'uniqueid' => $this->get_uniqueid(),
            'toast' => file_exists($CFG->dirroot . '/lib/amd/src/toast.js'),
        ];

        if (videotime_has_pro() && !$this->is_embed() && $nextactivitybutton = $this->get_next_activity_button()) {
            $renderer = $PAGE->get_renderer('mod_videotime');
            $context['next_activity_button_html'] = $renderer->render($nextactivitybutton);
        }

        if ($this->enabletabs) {
            $context['tabshtml'] = $output->render($this->tabs);
        }

        return $context;
    }

    /**
     * Returns external description
     *
     * @return external_description
     */
    public static function get_external_description(): external_description {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT),
            'course' => new \external_value(PARAM_INT),
            'name' => new \external_value(PARAM_RAW),
            'intro' => new \external_value(PARAM_RAW),
            'introformat' => new \external_value(PARAM_INT),
            'vimeo_url' => new \external_value(PARAM_URL),
            'video_description' => new \external_value(PARAM_RAW),
            'video_description_format' => new \external_value(PARAM_INT),
            'timemodified' => new \external_value(PARAM_INT),
            'completion_on_view_time' => new \external_value(PARAM_BOOL),
            'completion_on_view_time_second' => new \external_value(PARAM_INT),
            'completion_on_finish' => new \external_value(PARAM_BOOL),
            'completion_on_percent' => new \external_value(PARAM_BOOL),
            'completion_on_percent_value' => new \external_value(PARAM_INT),
            'autoplay' => new \external_value(PARAM_BOOL),
            'byline' => new \external_value(PARAM_BOOL),
            'color' => new \external_value(PARAM_TEXT),
            'height' => new \external_value(PARAM_TEXT),
            'maxheight' => new \external_value(PARAM_TEXT),
            'maxwidth' => new \external_value(PARAM_TEXT),
            'muted' => new \external_value(PARAM_BOOL),
            'playsinline' => new \external_value(PARAM_BOOL),
            'portrait' => new \external_value(PARAM_BOOL),
            'speed' => new \external_value(PARAM_BOOL),
            'title' => new \external_value(PARAM_BOOL),
            'transparent' => new \external_value(PARAM_BOOL),
            'autopause' => new \external_value(PARAM_BOOL),
            'background' => new \external_value(PARAM_BOOL),
            'controls' => new \external_value(PARAM_BOOL),
            'pip' => new \external_value(PARAM_BOOL),
            'dnt' => new \external_value(PARAM_BOOL),
            'width' => new \external_value(PARAM_TEXT),
            'responsive' => new \external_value(PARAM_BOOL),
            'label_mode' => new \external_value(PARAM_INT),
            'viewpercentgrade' => new \external_value(PARAM_BOOL),
            'next_activity_button' => new \external_value(PARAM_BOOL),
            'next_activity_id' => new \external_value(PARAM_INT),
            'next_activity_auto' => new \external_value(PARAM_BOOL),
            'resume_playback' => new \external_value(PARAM_BOOL),
            'preview_picture' => new \external_value(PARAM_INT),
            'show_description' => new \external_value(PARAM_BOOL),
            'show_title' => new \external_value(PARAM_BOOL),
            'show_tags' => new \external_value(PARAM_BOOL),
            'show_duration' => new \external_value(PARAM_BOOL),
            'show_viewed_duration' => new \external_value(PARAM_BOOL),
            'columns' => new \external_value(PARAM_INT),
            'preventfastforwarding' => new \external_value(PARAM_BOOL),
        ]);
    }

    /**
     * Call plugins hook to setup page
     */
    public function setup_page() {
        if ($this->enabletabs) {
            $this->tabs->setup_page();
        }
    }

}
