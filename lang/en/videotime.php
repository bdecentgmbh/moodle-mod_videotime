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
 * Plugin strings are defined here.
 *
 * @package     mod_videotime
 * @category    string
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotime/lib.php');

$string['force'] = 'Force setting';
$string['force_help'] = 'If checked this default will override the instance setting.';
$string['activity_name'] = 'Activity name';
$string['activity_name_help'] = 'Name displayed in course for this Video Time activity module.';
$string['completion_on_finish'] = 'Completion on video finish';
$string['completion_on_percent'] = 'Completion on watch percentage';
$string['completion_on_view'] = 'Completion on view time';
$string['default'] = 'Default';
$string['embed_options'] = 'Embed options';
$string['embed_options_defaults'] = 'Default embed options';
$string['gradeitemnotcreatedyet'] = 'A gradebook item does not exist for this activity. Check <b>Set grade equal to view percentage</b> above, save, and edit this activity again to set grade category and passing grade.';
$string['invalid_session_state'] = 'Invalid session state.';
$string['label_mode'] = 'Label mode';
$string['label_mode_help'] = 'Embed video on course layout, similar to the Label activity.';
$string['modulename'] = 'Video Time';
$string['modulenameplural'] = 'Video Time instances';
$string['next_activity'] = 'Next Activity';
$string['next_activity_in_course'] = 'Default: Next Activity In Course';
$string['next_activity_button'] = 'Enable Next Activity Button';
$string['next_activity_button_help'] = 'Display a button above the video which links to the next activity the user should complete.';
$string['option_autoplay'] = 'Autoplay';
$string['option_autoplay_help'] = 'Automatically start playback of the video. Note that this won’t work on some devices or browsers that block it.';
$string['option_byline'] = 'Byline';
$string['option_byline_help'] = 'Show the byline on the video.';
$string['option_color'] = 'Color';
$string['option_color_help'] = 'Specify the color of the video controls. Colors may be overridden by the embed settings of the video.';
$string['option_height'] = 'Height';
$string['option_height_help'] = 'The exact height of the video. Defaults to the height of the largest available version of the video.';
$string['option_maxheight'] = 'Max height';
$string['option_maxheight_help'] = 'Same as height, but video will not exceed the native size of the video.';
$string['option_maxwidth'] = 'Max width';
$string['option_maxwidth_help'] = 'Same as width, but video will not exceed the native size of the video.';
$string['option_muted'] = 'Muted';
$string['option_muted_help'] = 'Mute this video on load. Required to autoplay in certain browsers.';
$string['option_playsinline'] = 'Plays inline';
$string['option_playsinline_help'] = 'Play video inline on mobile devices, to automatically go fullscreen on playback set this parameter to false.';
$string['option_portrait'] = 'Portrait';
$string['option_portrait_help'] = 'Show the portrait on the video.';
$string['option_responsive'] = 'Responsive';
$string['option_responsive_help'] = 'If checked video player will be responsive and adapt to page or screen size.';
$string['option_speed'] = 'Speed';
$string['option_speed_help'] = 'Show the speed controls in the preferences menu and enable playback rate API (available to PRO and Business accounts).';
$string['option_title'] = 'Title';
$string['option_title_help'] = 'Show the title on the video.';
$string['option_transparent'] = 'Transparent';
$string['option_transparent_help'] = 'The responsive player and transparent background are enabled by default, to disable set this parameter to false.';
$string['option_width'] = 'Width';
$string['option_width_help'] = 'The exact width of the video. Defaults to the width of the largest available version of the video.';
$string['option_forced'] = '{$a->option} is globally forced to: {$a->value}';
if (!file_exists($CFG->dirroot . '/mod/videotime/plugin/pro')) {
    $string['modulename_help'] = 'The Video Time activity enables the teacher
<ul>
    <li>to easily embed videos from Vimeo, just by adding the url</li>
    <li>to add content above and below of the video player.</li>
</ul>

Video Time Pro has advanced features to
<ul>
    <li>track the user’s viewing time using activity completion</li>
    <li>get insights about each user’s viewing time</li>
    <li>set default embed options for the plugin</li>
    <li>and override the instances\' embed options globally.</li>
</ul>

Get Video Time Pro now on <a href="https://bdecent.de/products/videotimepro">https://bdecent.de/products/videotimepro</a>.

We are constantly improving the plugin, so stay tuned for upcoming versions. You can see what we’re working on and add feature requests in our public roadmap on <a href="https://bdecent.de/products/videotimepro/roadmap">https://bdecent.de/products/videotimepro/roadmap</a>.

Please let us know if you have any feedback for us.
';
} else {
    $string['modulename_help'] = 'The Video Time Pro activity enables the teacher
<ul>
    <li>to easily embed videos from Vimeo, just by adding the url</li>
    <li>to add content above and below of the video player</li>
    <li>track the user’s viewing time using activity completion</li>
    <li>get insights about each user’s viewing time</li>
    <li>set default embed options for the plugin</li>
    <li>and override the instances\' embed options globally.</li>
</ul>

We are constantly improving the plugin, so stay tuned for upcoming versions. You can see what we’re working on and add feature requests in our public roadmap on <a href="https://bdecent.de/products/videotimepro/roadmap">https://bdecent.de/products/videotimepro/roadmap</a>.

Please let us know if you have any feedback for us.
';
}
$string['pluginname'] = 'Video Time';
$string['pluginadministration'] = 'Video Time administration';
$string['preview_image'] = 'Preview image';
$string['preview_image_help'] = 'Image displayed for user.';
$string['resume_playback'] = 'Resume Playback';
$string['resume_playback_help'] = 'Automatically resume video when user returns to activity. Playback starts where the user left off.';
$string['seconds'] = 'Seconds';
$string['session_not_found'] = 'User session not found.';
$string['showdescription'] = 'Display description';
$string['showdescription_help'] = 'The description is displayed above the video and can be shown in the course page.';
$string['state'] = 'State';
$string['state_finished'] = 'Finished';
$string['state_help'] = 'Has the user finished the video?';
$string['state_incomplete'] = 'Incomplete';
$string['subplugintype_videotimeplugin'] = 'Video Time Plugin';
$string['subplugintype_videotimeplugin_plural'] = 'Video Time Plugins';
$string['viewpercentgrade'] = 'Set grade equal to view percentage.';
$string['viewpercentgrade_help'] = 'Create grade item for this video. Student will receive a grade equal to their view percentage of the video.';
$string['views'] = 'Views';
$string['views_help'] = 'Number of times the activity has been viewed.';
$string['view_report'] = 'View report';
$string['videotime:view_report'] = 'View report (Pro only)';
$string['video_description'] = 'Notes';
$string['video_description_help'] = 'Notes are displayed below the video.';
$string['videotime:addinstance'] = 'Add a new Video Time module';
$string['videotime:view'] = 'View Video Time video';
$string['vimeo_url'] = 'Vimeo URL';
$string['vimeo_url_help'] = 'Full URL of Vimeo video.';
$string['vimeo_url_invalid'] = 'Vimeo URL is invalid. Copy directly from web browser.';
$string['vimeo_url_missing'] = 'Vimeo URL is not set.';
$string['watch_time'] = 'Watch time';
$string['watch_time_help'] = 'How long the student has watched the video in total (in 5s steps).';
$string['watch_percent'] = 'Watch percent';
$string['watch_percent_help'] = 'The furthest moment in the video the student has watched.';
