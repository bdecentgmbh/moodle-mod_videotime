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
 * Define all the backup steps that will be used by the backup_videotime_activity_task
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete videotime structure for backup, with file and id annotations
 *
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_videotime_activity_structure_step extends backup_activity_structure_step {
    /**
     * Annotate files from plugin configuration
     * @param backup_nested_element $videotime the backup structure of the activity
     * @param string $subtype the plugin type to handle
     * @return void
     */
    protected function annotate_plugin_config_files(backup_nested_element $videotime, $subtype) {
        $pluginmanager = new \mod_videotime\plugin_manager($subtype);
        $plugins = $pluginmanager->get_sorted_plugins_list();
        foreach ($plugins as $plugin) {
            $component = $subtype . '_' . $plugin;
            if ($subtype == 'videotimetab') {
                $classname = '\\' . $component . '\\tab';
                $areas = $classname::get_config_file_areas();
            } else {
                $areas = component_callback($component, 'config_file_areas', [], []);
            }
            foreach ($areas as $area) {
                $videotime->annotate_files($component, $area, null);
            }
        }
    }

    /**
     * Defines the structure of the 'videotime' element inside the xml file
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        global $CFG;

        require_once($CFG->dirroot . '/mod/videotime/lib.php');

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $module = new backup_nested_element('videotime', ['id'], [
            'course',
            'name',
            'intro',
            'introformat',
            'vimeo_url',
            'video_description',
            'video_description_format',
            'timemodified',
            'completion_on_view_time',
            'completion_on_view_time_second',
            'completion_on_finish',
            'completion_on_percent',
            'completion_on_percent_value',
            'completion_hide_detail',
            'label_mode',
            'viewpercentgrade',
            'resume_playback',
            'preview_picture',
            'show_description_in_player',
            'enabletabs',
        ]);

        // Build the tree.

        // Define elements for tab subplugin settings.
        $this->add_subplugin_structure('videotimetab', $module, true);

        // Define elements for plugin subplugin settings.
        $this->add_subplugin_structure('videotimeplugin', $module, true);

        // Define sources.
        $module->set_source_table('videotime', ['id' => backup::VAR_ACTIVITYID]);

        // Define file annotations.
        $module->annotate_files('mod_videotime', 'intro', null); // This file area hasn't itemid.
        $module->annotate_files('mod_videotime', 'video_description', null); // This file area hasn't itemid.

        $this->annotate_plugin_config_files($module, 'videotimetab');
        $this->annotate_plugin_config_files($module, 'videotimeplugin');

        // Return the root element (videotime), wrapped into standard activity structure.
        return $this->prepare_activity_structure($module);
    }
}
