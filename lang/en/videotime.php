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

$string['activity_name'] = 'Activity name';
$string['activity_name_help'] = 'Name displayed in course for this Video Time activity module.';
$string['albums'] = 'Albums';
$string['api_not_authenticated'] = 'Vimeo API is not authenticated.';
$string['api_not_configured'] = 'Vimeo API is not configured.';
$string['apply'] = 'Apply';
$string['authenticated'] = 'Authenticated';
$string['authenticate_vimeo'] = 'Authenticate with Vimeo';
$string['authenticate_vimeo_success'] = 'Vimeo authentication successful. You can now use features that rely on the Vimeo API.';
$string['browsevideos'] = 'Browse videos';
$string['choose_video'] = 'Choose Video';
$string['choose_video_confirm'] = 'Are you sure you want to choose the video';
$string['cleanupalbumsandtags'] = 'Cleanup albums and tags';
$string['client_id'] = 'Vimeo Client ID';
$string['client_id_help'] = 'Client ID is generated when you create an "App" under your Vimeo account. Go to https://developer.vimeo.com/apps/new to start this process.';
$string['client_secret'] = 'Vimeo Client Secret';
$string['client_secret_help'] = 'Client Secret is generated when you create an "App" under your Vimeo account. Go to https://developer.vimeo.com/apps/new to start this process.';
$string['columns'] = 'Columns';
$string['columns_help'] = 'Choose the width for this video when displayed in preview mode. The number of columns is how many videos can be displayed in a row.';
$string['completion_on_finish'] = 'Completion on video finish';
$string['completion_on_percent'] = 'Completion on watch percentage';
$string['completion_on_view'] = 'Completion on view time';
$string['configure_vimeo_first'] = 'You must configure a Vimeo App before authenticating.';
$string['configure_vimeo_help'] = '<ol><li>Go to <a href="https://developer.vimeo.com/apps/new">https://developer.vimeo.com/apps/new</a> and login with your Vimeo account</li>
<li>Enter a name and description for your app. Example: Video Time Repository API</li>
<li>Ensure the checkbox "No. The only Vimeo accounts that will have access to the app are my own" is checked</li>
<li>Agree to Vimeo\'s Terms of Service and click "Create App"</li>
<li>You should now be taken to your new app</li>
<li>Click "Edit settings"</li>
<li>Enter an App description, this will be displayed to admins when authenticating with Vimeo</li>
<li>Enter App URL, it must be set to <b>{$a->redirect_url}</b></li>
<li>Click "Update"</li>
<li>Add a callback URL, it must be set to <b>{$a->redirect_url}</b></li>
<li>Copy down the Client Identifier (near the top) and the Client Secret (Manage App Secrets)</li>
<li>Enter Client ID and Client Secret <a href="{$a->configure_url}">here</a></li></ol>';
$string['confirmation'] = 'Confirmation';
$string['create_vimeo_app'] = 'Create Vimeo App';
$string['default'] = 'Default';
$string['deletesessiondata'] = 'Delete session data';
$string['discover_videos'] = 'Discover Vimeo videos';
$string['discovering_videos'] = 'Discovering {$a->count} videos';
$string['display_options'] = 'Display options';
$string['done'] = 'Done';
$string['duration'] = 'Duration';
$string['embed_options'] = 'Embed options';
$string['embed_options_defaults'] = 'Default embed options';
$string['embeds'] = 'Embeds';
$string['force'] = 'Force setting';
$string['force_help'] = 'If checked this default will override the instance setting.';
$string['goback'] = 'Go back';
$string['gradeitemnotcreatedyet'] = 'A gradebook item does not exist for this activity. Check <b>Set grade equal to view percentage</b> above, save, and edit this activity again to set grade category and passing grade.';
$string['invalid_session_state'] = 'Invalid session state.';
$string['insert_video_metadata'] = 'Insert metadata from video (may override activity settings)';
$string['label_mode'] = 'Label mode';
$string['mode'] = 'Mode';
$string['mode_help'] = '<b>Normal mode</b>: Displays the standard activity link, no extras on course page.<br>
<b>Label mode</b>: Embed video on course layout, similar to the Label activity.<br>
<b>Preview image mode</b>: Displays video thumbnail on course page that links to activity (Video Time Repository only).';
$string['modulename'] = 'Video Time';
$string['modulenameplural'] = 'Video Time instances';
$string['more'] = 'More';
$string['needs_authentication'] = 'Needs reauthentication';
$string['next_activity'] = 'Next Activity';
$string['next_activity_auto'] = 'Automatically Go To Next Activity';
$string['next_activity_auto_help'] = 'Automatically load the next activity when the student completes the video.';
$string['next_activity_in_course'] = 'Default: Next Activity In Course';
$string['next_activity_button'] = 'Enable Next Activity Button';
$string['next_activity_button_help'] = 'Display a button above the video which links to the next activity the user should complete.';
$string['normal_mode'] = 'Normal mode';
$string['not_authenticated'] = 'Not authenticated';
$string['of'] = 'of';
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
$string['preview_picture'] = 'Preview image';
$string['preview_picture_help'] = 'Image displayed for user.';
$string['preview_mode'] = 'Preview image mode';
$string['privacy:metadata'] = 'The Video Time activity module does not store any personal data.';
$string['process_videos'] = 'Process videos';
$string['process_videos_help'] = 'Videos will be processed via scheduled task. For larger Vimeo accounts, it may take time to fully process all videos.';
$string['pull_from_vimeo'] = 'Pull Metadata from Vimeo';
$string['pull_from_vimeo_invalid_videoid'] = 'Could not determine Video ID. Ensure you have entered a Vimeo URL (Example: https://vimeo.com/635473456).';
$string['pull_from_vimeo_loading'] = 'Pulling Metadata from Vimeo...';
$string['pull_from_vimeo_success'] = 'Metadata was successfully pulled from Vimeo. Some of the activity settings have been overridden.';
$string['rate_limit'] = 'Vimeo API request limit';
$string['refreshpage'] = 'Please refresh your page to view duplicated activity';
$string['results'] = 'results';
$string['resume_playback'] = 'Resume Playback';
$string['resume_playback_help'] = 'Automatically resume video when user returns to activity. Playback starts where the user left off.';
$string['run_discovery_task'] = 'Run this "Discover Vimeo videos" task to begin pulling in your video data. Otherwise you can wait until it runs automatically.';
$string['estimated_request_time'] = 'Estimated time remaining';
$string['search_help'] = 'Search name, description, albums, tags...';
$string['seconds'] = 'Seconds';
$string['session_not_found'] = 'User session not found.';
$string['set_client_id_and_secret'] = 'Set Client ID and Secret';
$string['settings'] = 'Video Time settings';
$string['setup_repository'] = 'Setup repository';
$string['show_title'] = 'Show title';
$string['show_description'] = 'Show description';
$string['show_tags'] = 'Show tags';
$string['show_duration'] = 'Show duration';
$string['show_viewed_duration'] = 'Show viewed duration';
$string['showdescription'] = 'Display description';
$string['showdescription_help'] = 'The description is displayed above the video and can be shown in the course page.';
$string['showing'] = 'Showing';
$string['state'] = 'State';
$string['state_finished'] = 'Finished';
$string['state_help'] = 'Has the user finished the video?';
$string['state_incomplete'] = 'Incomplete';
$string['status'] = 'Status';
$string['store_pictures'] = 'Store thumbnails';
$string['store_pictures_help'] = 'If enabled, the Vimeo thumbnails will be stored locally. Otherwise the images will be delivered from Vimeo externally.';
$string['subplugintype_videotimeplugin'] = 'Video Time Plugin';
$string['subplugintype_videotimeplugin_plural'] = 'Video Time Plugins';
$string['taskscheduled'] = 'Task scheduled for next cron run';
$string['todo'] = 'TODO';
$string['totara_video_discovery_help'] = '<p>You may execute this task manually by running a CLI command:</p> 
<p><b>/usr/bin/php admin/tool/task/cli/schedule_task.php --execute=\\\\videotimeplugin_repository\\\\task\\\\discover_videos</b></p> 
<p>Otherwise you may have to wait until the scheduled task runs.</p>
<p>You can also run the command to pull in album information manually (instead of waiting):</p>
<p><b>/usr/bin/php admin/tool/task/cli/schedule_task.php --execute=\\\\videotimeplugin_repository\\\\task\\\\update_albums</b></p>';
$string['update_albums'] = 'Update video albums';
$string['upgrade_vimeo_account'] = 'NOTICE: Consider upgrading your Vimeo account. Your API request limit is too low.';
$string['use'] = 'Use';
$string['viewpercentgrade'] = 'Set grade equal to view percentage.';
$string['viewpercentgrade_help'] = 'Create grade item for this video. Student will receive a grade equal to their view percentage of the video.';
$string['views'] = 'Views';
$string['views_help'] = 'Number of times the activity has been viewed.';
$string['view_report'] = 'View report';
$string['videotime:view_report'] = 'View report (Pro only)';
$string['video_description'] = 'Notes';
$string['video_description_help'] = 'Notes are displayed below the video.';
$string['videos_discovered'] = 'Videos discovered';
$string['videos_processed'] = 'Videos processed';
$string['videotime:addinstance'] = 'Add a new Video Time module';
$string['videotime:view'] = 'View Video Time video';
$string['vimeo_url'] = 'Vimeo URL';
$string['vimeo_url_help'] = 'Full URL of Vimeo video.';
$string['vimeo_url_invalid'] = 'Vimeo URL is invalid. Copy directly from web browser.';
$string['vimeo_url_missing'] = 'Vimeo URL is not set.';
$string['vimeo_video_not_found'] = 'Video does not exist in database.';
$string['vimeo_video_not_processed'] = 'Video has not been fully processed yet. Please check back later.';
$string['watch_time'] = 'Watch time';
$string['watch_time_help'] = 'How long the student has watched the video in total (in 5s steps).';
$string['watch_percent'] = 'Watch percent';
$string['watch_percent_help'] = 'The furthest moment in the video the student has watched.';
$string['with_play_button'] = 'with play button';
$string['vimeo_overview'] = 'Overview and setup';