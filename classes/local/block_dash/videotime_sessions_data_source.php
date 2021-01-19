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
 * @package    mod_videotime
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\local\block_dash;

use block_dash\local\dash_framework\query_builder\builder;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\structure\user_table;
use block_dash\local\data_grid\filter\bool_filter;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\data_grid\filter\current_course_condition;
use block_dash\local\data_grid\filter\date_filter;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_grid\filter\filter_collection_interface;
use block_dash\local\data_grid\filter\logged_in_user_condition;
use block_dash\local\data_source\abstract_data_source;
use context;
use local_dash\data_grid\filter\category_field_filter;
use local_dash\data_grid\filter\course_category_condition;
use local_dash\data_grid\filter\course_field_filter;
use local_dash\data_grid\filter\customfield_filter;
use local_dash\data_grid\filter\my_enrolled_courses_condition;
use local_dash\data_grid\filter\parent_role_condition;
use local_dash\local\dash_framework\structure\course_table;
use mod_videotime\local\dash_framework\structure\videotime_session_table;
use mod_videotime\local\dash_framework\structure\videotime_table;

class videotime_sessions_data_source extends abstract_data_source {

    /**
     * Constructor.
     *
     * @param context $context
     */
    public function __construct(context $context) {
        $this->add_table(new videotime_session_table());
        $this->add_table(new videotime_table());
        $this->add_table(new user_table());
        $this->add_table(new course_table());
        parent::__construct($context);
    }

    /**
     * @return builder
     */
    public function get_query_template(): builder {
        global $DB;

        $module = $DB->get_field('modules', 'id', ['name' => 'videotime']);

        $builder = new builder();
        $builder
            ->select('vts.id', 'vts_id')
            ->from('videotime', 'vt')
            ->join('course_modules', 'cm', 'instance', 'vt.id', join::TYPE_INNER_JOIN, ['cm.module' => $module])
            ->join('videotime_session', 'vts', 'module_id', 'cm.id')
            ->join('user', 'u', 'id', 'vts.user_id')
            ->join('course', 'c', 'id', 'vt.course')
            ->join('course_categories', 'cc', 'id', 'c.category')
            ->groupby('vt.id')->groupby('vts.user_id');

        $filterpreferences = $this->get_preferences('filters');

        if (class_exists('\core_course\customfield\course_handler')) {
            $handler = \core_course\customfield\course_handler::create();
            foreach ($handler->get_fields() as $field) {
                $alias = 'c_f_' . strtolower($field->get('shortname'));
                // Only join custom field table if the filter is enabled.
                if (isset($filterpreferences[$alias]) && $filterpreferences[$alias]['enabled']) {
                    $builder->join('customfield_data', $alias, 'instanceid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($alias, "$alias.fieldid = " . $field->get('id'));
                }
            }
        } else if (block_dash_is_totara()) {
            global $DB;

            foreach ($DB->get_records('course_info_field') as $field) {
                $alias = 'c_f_' . strtolower($field->shortname);
                // Only join custom field table if the filter is enabled.
                if (isset($filterpreferences[$alias]) && $filterpreferences[$alias]['enabled']) {
                    $builder->join('course_info_data', $alias, 'courseid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($alias, "$alias.fieldid = " . $field->get('id'));
                }
            }
        }

        return $builder;
    }

    /**
     * @return filter_collection_interface
     */
    public function build_filter_collection()
    {
        $filter_collection = new filter_collection(get_class($this), $this->get_context());

        $filter_collection->add_filter(new category_field_filter('cc', 'cc.id', get_string('category')));

        $filter_collection->add_filter(new course_field_filter('c', 'c.id', get_string('course')));

        if (class_exists('\core_course\customfield\course_handler')) {
            $handler = \core_course\customfield\course_handler::create();
            foreach ($handler->get_fields() as $field) {

                $alias = 'c_f_' . strtolower($field->get('shortname'));
                $select = $alias . '.value';

                switch ($field->get('type')) {
                    case 'checkbox':
                        $definitions[] = new bool_filter($alias, $select, $field->get_formatted_name());
                        break;
                    case 'date':
                        $filter_collection->add_filter(new date_filter($alias, $select, date_filter::DATE_FUNCTION_FLOOR,
                            $field->get_formatted_name()));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $filter_collection->add_filter(new customfield_filter($alias, $select, $field,
                            $field->get_formatted_name()));
                        break;
                }
            }
        } else if (block_dash_is_totara()) {
            global $DB;

            foreach ($DB->get_records('course_info_field') as $field) {

                $alias = 'c_f_' . strtolower($field->shortname);
                $select = $alias . '.data';

                switch ($field->datatype) {
                    case 'checkbox':
                        $definitions[] = new bool_filter($alias, $select, $field->fullname);
                        break;
                    case 'date':
                        $filter_collection->add_filter(new date_filter($alias, $select, date_filter::DATE_FUNCTION_FLOOR,
                            $field->fullname));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $filter_collection->add_filter(new customfield_filter($alias, $select, $field,
                            $field->fullname));
                        break;
                }
            }
        }

        $filter_collection->add_filter(new current_course_condition('c_current_course', 'c.id'));

        $filter_collection->add_filter(new course_condition('c_course', 'c.id'));

        $filter_collection->add_filter(new my_enrolled_courses_condition('my_enrolled_courses', 'c.id'));

        $filter_collection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        $filter_collection->add_filter(new parent_role_condition('parentrole', 'u.id'));

        $filter_collection->add_filter(new logged_in_user_condition('logged_in_user', 'u.id'));

        return $filter_collection;
    }
}