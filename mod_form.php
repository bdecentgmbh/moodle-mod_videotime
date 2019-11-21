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

            $needs_setup = false;
            try {
                $api = new \videotimeplugin_repository\api();
            } catch (\videotimeplugin_repository\exception\api_not_configured $e) {
                $needs_setup = true;
            } catch (\videotimeplugin_repository\exception\api_not_authenticated $e) {
                $needs_setup = true;
            }

            $group = [];
            $group[] = $mform->createElement('text', 'vimeo_url', get_string('vimeo_url', 'videotime'));
            if (!$needs_setup) {
                $group[] = $mform->createElement('button', 'pull_from_vimeo', get_string('pull_from_vimeo', 'videotime'));
            }
            $mform->addGroup($group, '', get_string('vimeo_url', 'videotime'));

            $group = [];
            if (!$needs_setup) {
                $group[] = $mform->createElement('static', 'choose_video_label', '', '- or -');
                $group[] = $mform->createElement('button', 'choose_video', get_string('choose_video', 'videotime'));
            } else if (is_siteadmin()) {
                $group[] = $mform->createElement('static', 'choose_video_label', '', '- or -');
                $group[] = $mform->createElement('html',
                    html_writer::link(new moodle_url('/mod/videotime/plugin/repository/overview.php'),
                        get_string('setup_repository', 'videotime')));
            }
            $mform->addGroup($group);

            $albums = array_values($DB->get_records('videotime_vimeo_album', null, 'name'));
            $tags = array_values($DB->get_records('videotime_vimeo_tag', null, 'name'));

            $PAGE->requires->js_call_amd('videotimeplugin_repository/mod_form', 'init',
                [$albums, $tags, videotime_is_totara(), $this->context->id]);
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
            $group = [];
            $group[] = $mform->createElement('radio', 'label_mode', '', get_string('normal_mode', 'videotime'), 0);
            $group[] = $mform->createElement('radio', 'label_mode', '', get_string('label_mode', 'videotime'), 1);
            if (videotime_has_repository()) {
                $group[] = $mform->createElement('radio', 'label_mode', '', get_string('preview_mode', 'videotime'), 2);
            }

            $mform->addGroup($group, 'modegroup', get_string('mode', 'videotime'), array('<br>'), false);
            $mform->addHelpButton('modegroup', 'mode', 'videotime');
            $mform->setDefault('label_mode', get_config('videotime', 'label_mode'));
            if (get_config('videotime', 'label_mode_force')) {
                $mform->addElement('static', 'label_mode_forced', '', get_string('option_forced', 'videotime', [
                    'option' => get_string('label_mode', 'videotime'),
                    'value' => get_config('videotime', 'label_mode')
                ]));
                $mform->disabledIf('label_mode', 'disable', 'eq', 1);
            }

            if (videotime_has_repository()) {
                $group = [];
                $group[] = $mform->createElement('advcheckbox', 'show_title', '', get_string('show_title', 'videotime'));
                $mform->setDefault('show_title', 1);
                $mform->hideIf('show_title', 'label_mode', 'noeq', 2);
                $mform->setDefault('show_title', get_config('videotime', 'show_title'));
                if (get_config('videotime', 'show_title_force')) {
                    $group[] = $mform->createElement('static', 'show_title_forced', '', get_string('option_forced', 'videotime', [
                        'option' => get_string('show_title', 'videotime'),
                        'value' => get_config('videotime', 'show_title')
                    ]));
                    $mform->disabledIf('show_title', 'disable', 'eq', 1);
                }

                $group[] = $mform->createElement('advcheckbox', 'show_description', '', get_string('show_description', 'videotime'));
                $mform->setDefault('show_description', 1);
                $mform->hideIf('show_description', 'label_mode', 'noeq', 2);
                $mform->setDefault('show_description', get_config('videotime', 'show_description'));
                if (get_config('videotime', 'show_description_force')) {
                    $group[] = $mform->createElement('static', 'show_description_forced', '', get_string('option_forced', 'videotime', [
                        'option' => get_string('show_description', 'videotime'),
                        'value' => get_config('videotime', 'show_description')
                    ]));
                    $mform->disabledIf('show_description', 'disable', 'eq', 1);
                }

                $group[] = $mform->createElement('advcheckbox', 'show_tags', '', get_string('show_tags', 'videotime'));
                $mform->setDefault('show_tags', 1);
                $mform->hideIf('show_tags', 'label_mode', 'noeq', 2);
                $mform->setDefault('show_tags', get_config('videotime', 'show_tags'));
                if (get_config('videotime', 'show_tags_force')) {
                    $group[] = $mform->createElement('static', 'show_tags_forced', '', get_string('option_forced', 'videotime', [
                        'option' => get_string('show_tags', 'videotime'),
                        'value' => get_config('videotime', 'show_tags')
                    ]));
                    $mform->disabledIf('show_tags', 'disable', 'eq', 1);
                }

                $group[] = $mform->createElement('advcheckbox', 'show_duration', '', get_string('show_duration', 'videotime'));
                $mform->setDefault('show_duration', 1);
                $mform->hideIf('show_duration', 'label_mode', 'noeq', 2);
                $mform->setDefault('show_duration', get_config('videotime', 'show_duration'));
                if (get_config('videotime', 'show_duration_force')) {
                    $group[] = $mform->createElement('static', 'show_duration_forced', '', get_string('option_forced', 'videotime', [
                        'option' => get_string('show_duration', 'videotime'),
                        'value' => get_config('videotime', 'show_duration')
                    ]));
                    $mform->disabledIf('show_duration', 'disable', 'eq', 1);
                }

                $group[] = $mform->createElement('advcheckbox', 'show_viewed_duration', '', get_string('show_viewed_duration', 'videotime'));
                $mform->setDefault('show_viewed_duration', 1);
                $mform->hideIf('show_viewed_duration', 'label_mode', 'noeq', 2);
                $mform->setDefault('show_viewed_duration', get_config('videotime', 'show_viewed_duration'));
                if (get_config('videotime', 'show_viewed_duration_force')) {
                    $group[] = $mform->createElement('static', 'show_viewed_duration_forced', '', get_string('option_forced', 'videotime', [
                        'option' => get_string('show_viewed_duration', 'videotime'),
                        'value' => get_config('videotime', 'show_viewed_duration')
                    ]));
                    $mform->disabledIf('show_viewed_duration', 'disable', 'eq', 1);
                }

                $mform->addGroup($group, 'displaygroup', get_string('display_options', 'videotime'), array('<br>'), false);

                $mform->addElement('select', 'columns', get_string('columns', 'videotime'), [
                    1 => '1 (100% width)',
                    2 => '2 (50% width)',
                    3 => '3 (33% width)',
                    4 => '4 (25% width'
                ]);
                $mform->setType('columns', PARAM_INT);
                $mform->addHelpButton('columns', 'columns', 'videotime');
                $mform->hideIf('columns', 'label_mode', 'noeq', 2);
                $mform->setDefault('columns', get_config('videotime', 'columns'));
                if (get_config('videotime', 'columns_force')) {
                    $mform->hideIf('columns_forced', 'label_mode', 'noeq', 2);
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
                $mform->hideIf('preview_picture', 'label_mode', 'noeq', 2);
                $mform->setDefault('preview_picture', get_config('videotime', 'preview_picture'));
                if (get_config('videotime', 'preview_picture_force')) {
                    $mform->hideIf('preview_picture_forced', 'label_mode', 'noeq', 2);
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

        if (videotime_has_pro()) {
            $mform->addElement('advcheckbox', 'resume_playback', get_string('resume_playback', 'videotime'));
            $mform->addHelpButton('resume_playback', 'resume_playback', 'videotime');
            $mform->setType('resume_playback', PARAM_BOOL);
            $mform->setDefault('resume_playback', get_config('videotime', 'resume_playback'));
            if (get_config('videotime', 'resume_playback_force')) {
                $mform->addElement('static', 'resume_playback_forced', '', get_string('option_forced', 'videotime', [
                    'option' => get_string('resume_playback', 'videotime'),
                    'value' => get_config('videotime', 'resume_playback') ? get_string('yes') : get_string('no')
                ]));
                $mform->disabledIf('resume_playback', 'disable', 'eq', 1);
            }

            $mform->addElement('advcheckbox', 'next_activity_button', get_string('next_activity_button', 'videotime'));
            $mform->addHelpButton('next_activity_button', 'next_activity_button', 'videotime');
            $mform->setType('next_activity_button', PARAM_BOOL);
            $mform->setDefault('next_activity_button', get_config('videotime', 'next_activity_button'));
            if (get_config('videotime', 'next_activity_button_force')) {
                $mform->addElement('static', 'next_activity_button_forced', '', get_string('option_forced', 'videotime', [
                    'option' => get_string('next_activity_button', 'videotime'),
                    'value' => get_config('videotime', 'next_activity_button') ? get_string('yes') : get_string('no')
                ]));
                $mform->disabledIf('next_activity_button', 'disable', 'eq', 1);
            }

            $modinfo = get_fast_modinfo($COURSE->id);
            $modoptions = [-1 => get_string('next_activity_in_course', 'videotime')];
            foreach ($modinfo->get_cms() as $cm) {
                if (isset($this->_cm->id) && $this->_cm->id == $cm->id) {
                    continue;
                }
                $modoptions[$cm->id] = $cm->name;
            }

            $mform->addElement('select', 'next_activity_id', get_string('next_activity', 'videotime'), $modoptions);
            $mform->setType('next_activity_id', PARAM_INT);
            $mform->hideIf('next_activity_id', 'next_activity_button');

            $mform->addElement('advcheckbox', 'next_activity_auto', get_string('next_activity_auto', 'videotime'));
            $mform->addHelpButton('next_activity_auto', 'next_activity_auto', 'videotime');
            $mform->setType('next_activity_auto', PARAM_BOOL);
            $mform->hideIf('next_activity_auto', 'next_activity_button');
            $mform->setDefault('next_activity_auto', get_config('videotime', 'next_activity_auto'));
            if (get_config('videotime', 'next_activity_auto_force')) {
                $mform->addElement('static', 'next_activity_auto_forced', '', get_string('option_forced', 'videotime', [
                    'option' => get_string('next_activity_auto', 'videotime'),
                    'value' => get_config('videotime', 'next_activity_auto') ? get_string('yes') : get_string('no')
                ]));
                $mform->disabledIf('next_activity_auto', 'disable', 'eq', 1);
            }
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
        if (get_config('videotime', 'responsive_force')) {
            $mform->addElement('static', 'responsive_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_responsive', 'videotime'),
                'value' => get_config('videotime', 'responsive')
            ]));
            $mform->disabledIf('responsive', 'disable', 'eq', 1);
        }

        $mform->addElement('text', 'height', get_string('option_height', 'videotime'));
        $mform->setType('height', PARAM_INT);
        $mform->addHelpButton('height', 'option_height', 'videotime');
        $mform->disabledIf('height', 'responsive', 'checked');
        $mform->setDefault('height', get_config('videotime', 'height'));
        if (get_config('videotime', 'height_force')) {
            $mform->addElement('static', 'height_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_height', 'videotime'),
                'value' => get_config('videotime', 'height')
            ]));
            $mform->disabledIf('height', 'disable', 'eq', 1);
        }

        $mform->addElement('text', 'width', get_string('option_width', 'videotime'));
        $mform->setType('width', PARAM_INT);
        $mform->addHelpButton('width', 'option_width', 'videotime');
        $mform->setDefault('width', get_config('videotime', 'width'));
        $mform->disabledIf('width', 'responsive', 'checked');
        if (get_config('videotime', 'width_force')) {
            $mform->addElement('static', 'width_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_width', 'videotime'),
                'value' => get_config('videotime', 'width')
            ]));
            $mform->disabledIf('width', 'disable', 'eq', 1);
        }

        $mform->addElement('text', 'maxheight', get_string('option_maxheight', 'videotime'));
        $mform->setType('maxheight', PARAM_INT);
        $mform->addHelpButton('maxheight', 'option_maxheight', 'videotime');
        $mform->setDefault('maxheight', get_config('videotime', 'maxheight'));
        $mform->disabledIf('maxheight', 'responsive', 'checked');
        if (get_config('videotime', 'maxheight_force')) {
            $mform->addElement('static', 'maxheight_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_maxheight', 'videotime'),
                'value' => get_config('videotime', 'maxheight')
            ]));
            $mform->disabledIf('maxheight', 'disable', 'eq', 1);
        }

        $mform->addElement('text', 'maxwidth', get_string('option_maxwidth', 'videotime'));
        $mform->setType('maxwidth', PARAM_INT);
        $mform->addHelpButton('maxwidth', 'option_maxwidth', 'videotime');
        $mform->setDefault('maxwidth', get_config('videotime', 'maxwidth'));
        $mform->disabledIf('maxwidth', 'responsive', 'checked');
        if (get_config('videotime', 'maxwidth_force')) {
            $mform->addElement('static', 'maxwidth_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_maxwidth', 'videotime'),
                'value' => get_config('videotime', 'maxwidth')
            ]));
            $mform->disabledIf('maxwidth', 'disable', 'eq', 1);
        }

        $mform->addElement('advcheckbox', 'autoplay', get_string('option_autoplay', 'videotime'));
        $mform->setType('autoplay', PARAM_BOOL);
        $mform->addHelpButton('autoplay', 'option_autoplay', 'videotime');
        $mform->setDefault('autoplay', get_config('videotime', 'autoplay'));
        if (get_config('videotime', 'autoplay_force')) {
            $mform->addElement('static', 'autoplay_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_autoplay', 'videotime'),
                'value' => get_config('videotime', 'autoplay')
            ]));
            $mform->disabledIf('autoplay', 'disable', 'eq', 1);
        }

        $mform->addElement('advcheckbox', 'byline', get_string('option_byline', 'videotime'));
        $mform->setType('byline', PARAM_BOOL);
        $mform->addHelpButton('byline', 'option_byline', 'videotime');
        $mform->setDefault('byline', get_config('videotime', 'byline'));
        if (get_config('videotime', 'byline_force')) {
            $mform->addElement('static', 'byline_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_byline', 'videotime'),
                'value' => get_config('videotime', 'byline')
            ]));
            $mform->disabledIf('byline', 'disable', 'eq', 1);
        }

        $mform->addElement('text', 'color', get_string('option_color', 'videotime'));
        $mform->setType('color', PARAM_TEXT);
        $mform->addHelpButton('color', 'option_color', 'videotime');
        $mform->setDefault('color', get_config('videotime', 'color'));
        if (get_config('videotime', 'color_force')) {
            $mform->addElement('static', 'color_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_color', 'videotime'),
                'value' => get_config('videotime', 'color')
            ]));
            $mform->disabledIf('color', 'disable', 'eq', 1);
        }

        $mform->addElement('advcheckbox', 'muted', get_string('option_muted', 'videotime'));
        $mform->setType('muted', PARAM_BOOL);
        $mform->addHelpButton('muted', 'option_muted', 'videotime');
        $mform->setDefault('muted', get_config('videotime', 'muted'));
        if (get_config('videotime', 'muted_force')) {
            $mform->addElement('static', 'muted_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_muted', 'videotime'),
                'value' => get_config('videotime', 'muted')
            ]));
            $mform->disabledIf('muted', 'disable', 'eq', 1);
        }

        $mform->addElement('advcheckbox', 'playsinline', get_string('option_playsinline', 'videotime'));
        $mform->setType('playsinline', PARAM_BOOL);
        $mform->addHelpButton('playsinline', 'option_playsinline', 'videotime');
        $mform->setDefault('playsinline', get_config('videotime', 'playsinline'));
        if (get_config('videotime', 'playsinline_force')) {
            $mform->addElement('static', 'playsinline_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_playsinline', 'videotime'),
                'value' => get_config('videotime', 'playsinline')
            ]));
            $mform->disabledIf('playsinline', 'disable', 'eq', 1);
        }

        $mform->addElement('advcheckbox', 'portrait', get_string('option_portrait', 'videotime'));
        $mform->setType('portrait', PARAM_BOOL);
        $mform->addHelpButton('portrait', 'option_portrait', 'videotime');
        $mform->setDefault('portrait', get_config('videotime', 'portrait'));
        if (get_config('videotime', 'portrait_force')) {
            $mform->addElement('static', 'portrait_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_portrait', 'videotime'),
                'value' => get_config('videotime', 'portrait')
            ]));
            $mform->disabledIf('portrait', 'disable', 'eq', 1);
        }

        $mform->addElement('advcheckbox', 'speed', get_string('option_speed', 'videotime'));
        $mform->setType('speed', PARAM_BOOL);
        $mform->addHelpButton('speed', 'option_speed', 'videotime');
        $mform->setDefault('speed', get_config('videotime', 'speed'));
        if (get_config('videotime', 'speed_force')) {
            $mform->addElement('static', 'speed_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_speed', 'videotime'),
                'value' => get_config('videotime', 'speed')
            ]));
            $mform->disabledIf('speed', 'disable', 'eq', 1);
        }

        $mform->addElement('advcheckbox', 'title', get_string('option_title', 'videotime'));
        $mform->setType('title', PARAM_BOOL);
        $mform->addHelpButton('title', 'option_title', 'videotime');
        $mform->setDefault('title', get_config('videotime', 'title'));
        if (get_config('videotime', 'title_force')) {
            $mform->addElement('static', 'title_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_title', 'videotime'),
                'value' => get_config('videotime', 'title')
            ]));
            $mform->disabledIf('title', 'disable', 'eq', 1);
        }

        $mform->addElement('advcheckbox', 'transparent', get_string('option_transparent', 'videotime'));
        $mform->setType('transparent', PARAM_BOOL);
        $mform->addHelpButton('transparent', 'option_transparent', 'videotime');
        $mform->setDefault('transparent', get_config('videotime', 'transparent'));
        if (get_config('videotime', 'transparent_force')) {
            $mform->addElement('static', 'transparent_forced', '', get_string('option_forced', 'videotime', [
                'option' => get_string('option_transparent', 'videotime'),
                'value' => get_config('videotime', 'transparent')
            ]));
            $mform->disabledIf('transparent', 'disable', 'eq', 1);
        }

        if (videotime_has_pro()) {
            // -------------------------------------------------------------------------------
            // Grade settings.
            $mform->addElement('header', 'modstandardgrade', get_string('grade'));
            global $COURSE, $OUTPUT;

            $mform->addElement('checkbox', 'viewpercentgrade', get_string('viewpercentgrade', 'videotime'));
            $mform->setType('viewpercentgrade', PARAM_BOOL);
            $mform->addHelpButton('viewpercentgrade', 'viewpercentgrade', 'videotime');

            $mform->addElement('select', 'gradecat', get_string('gradecategoryonmodform', 'grades'),
                grade_get_categories_menu($COURSE->id, false));
            $mform->addHelpButton('gradecat', 'gradecategoryonmodform', 'grades');
            $mform->disabledIf('gradecat', 'viewpercentgrade');

            // Grade to pass.
            $mform->addElement('text', 'gradepass', get_string('gradepass', 'grades'));
            $mform->addHelpButton('gradepass', 'gradepass', 'grades');
            $mform->setDefault('gradepass', '');
            $mform->setType('gradepass', PARAM_RAW);
            $mform->disabledIf('gradepass', 'viewpercentgrade');

            if ($this->_cm) {
                if (!grade_item::fetch(array('itemtype' => 'mod',
                    'itemmodule' => $this->_cm->modname,
                    'iteminstance' => $this->_cm->instance,
                    'itemnumber' => 0,
                    'courseid' => $COURSE->id))) {

                    $mform->addElement('static', 'gradewarning', '', $OUTPUT->notification(get_string('gradeitemnotcreatedyet', 'videotime'), 'warning'), null, ['id' => 'id_gradewarning']);
                    $mform->hideIf('gradewarning', 'viewpercentgrade', 'checked');
                }
            }
        }

        // Add standard elements.
        $this->standard_coursemodule_elements();

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

            return ['completion_on_view', 'completion_on_percent', 'completion_on_finish'];
        } else {
            // Remove completion on grade since grade settings are not displayed for free version.
            $mform->removeElement('completionusegrade');
        }

        return [];
    }

    public function completion_rule_enabled($data) {
        return (
            (!empty($data['completion_on_view_time']) && $data['completion_on_view_time_second'] != 0)) ||
            !empty($data['completion_on_finish'] ||
            (!empty($data['completion_on_percent']) && $data['completion_on_percent_value']));
    }

    /**
     * @param $data
     * @param $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files) {
        $errors = [];
        if (!isset($data['vimeo_url']) || empty($data['vimeo_url'])) {
            $errors['required'] = get_string('required');
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
        }
    }
}
