<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     videotimetab_interaction
 * @category    string
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action'] = 'Action';
$string['categoryhdr_help'] = 'Select a category of the question bank containing questions to be added below.';
$string['confirmresetattempt'] = 'Questions attempt reset';
$string['continue'] = 'Continue';
$string['countdown'] = 'Count down';
$string['countdown_help'] = 'The time in seconds a user has to click continue before the video automatically pauses.';
$string['cueno'] = 'Cue {no}';
$string['cues'] = 'Cues';
$string['cues_help'] = 'Select the start and end times for intervals in the video where the interaction should appear. Enter content to be displayed with the text editor. Also select the type of interaction. <dl><dt>Information</dt><dd>Displays content in the Interactions tab.</dd><dt>Continue</dt><dd>Requires the viewer to respond to continue playing.</dd></dl>';
$string['default'] = 'Default';
$string['default_help'] = 'Whether interactions tab is enabled by default';
$string['editprompts'] = 'Edit prompts';
$string['editquestions'] = 'Edit questions';
$string['endlessthanstart'] = 'The start time must be before the ending time.';
$string['eventanswersubmitted'] = 'Answer submitted';
$string['eventinteractionviewed'] = 'Interaction viewed';
$string['eventtimerexpired'] = 'Timer expired';
$string['howquestionsbehave_desc'] = 'The question behaviour used for questions displayed in a video.';
$string['information'] = 'Information';
$string['interaction:edit'] = 'Edit';
$string['interaction:editquestions'] = 'Edit questions';
$string['interaction:interact'] = 'Interact';
$string['interaction:resetattempt'] = 'Reset attempt';
$string['label'] = 'Interactions';
$string['maximumstarttime'] = 'Maxmum time {$a} seconds';
$string['nopause'] = 'No pause';
$string['pause'] = 'Pause video';
$string['pausetodo'] = 'Pause if incomplete';
$string['pluginname'] = 'Video Time Interactions tab';
$string['privacy:metadata'] = 'The Video Time Interaction tab plugin does not store any personal data.';
$string['prompteffect'] = 'Ratio';
$string['prompteffect_help'] = 'Percentage applied to increase the random interval between prompts after each successful response to an automatic continue prompt. If set to 100%, the average interval remains constant. If a prompt is missed, the average interval resets to its original setting.';
$string['questionno'] = 'Question {no}';
$string['questionshdr_help'] = 'Select the start and end times in the video where each question should appear. Select behaviour:<dl><dt>No pause</dt><dd>Video plays uninterupted</dd><dt>Pause if incomplete</dt><dd>Pauses if question should still be answered</dd><dt>Pause video</dt><dd>Always pauses video when question appears</dd></dl>Questions need to be added to the question bank before they can be selected.';
$string['randomprompt'] = 'Click to continue watching video';
$string['reset_help'] = 'Erase your answers to all questions and start again.';
$string['resetquestionattempt'] = 'Do you want to erase question attempts for "{$a}"?';
$string['spacing'] = 'Interval';
$string['spacing_desc'] = 'This provides default setting for prompts displayed randomly when video is playing. The time sets the average time for the first prompts to appear. The value may be changed in the course settings to adjust for video length.';
$string['spacing_help'] = 'Enable to set prompts to appear randomly when video is playing. The time affects the average time between prompts.';
$string['timeremaining'] = 'Time remaining {$a} s';
$string['timing'] = 'Timing';
