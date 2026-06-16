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
 * Defines backup_videotimetab_interaction_subplugin class
 *
 * @package     videotimetab_interaction
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines backup_videotimetab_interaction_subplugin class
 *
 * Provides the step to perform back up of sublugin data
 */
class backup_videotimetab_interaction_subplugin extends backup_subplugin {
    /**
     * Defined suplugin structure step
     */
    protected function define_videotime_subplugin_structure() {

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subplugintablesettings = new backup_nested_element(
            'videotimetab_interaction',
            null,
            ['spacing', 'videotime']
        );
        $cues = new backup_nested_element('videotimetab_interaction_cues');
        $cue = new backup_nested_element('videotimetab_interaction_cue', ['id'], [
            'action',
            'data',
            'endtime',
            'starttime',
        ]);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subplugintablesettings);
        $cues->add_child($cue);
        $subpluginwrapper->add_child($cues);

        // Set source to populate the data.
        $subplugintablesettings->set_source_table(
            'videotimetab_interaction',
            ['videotime' => backup::VAR_ACTIVITYID]
        );
        $cue->set_source_table(
            'videotimetab_interaction_cue',
            ['videotime' => backup::VAR_ACTIVITYID]
        );

        // Define file annotations.
        $cue->annotate_files('videotimetab_interaction', 'content', 'id');

        return $subplugin;
    }
}
