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
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_grid\filter\filter_collection_interface;
use block_dash\local\data_source\abstract_data_source;
use context;
use local_dash\data_grid\filter\my_dashboards_condition;
use local_dash\data_grid\filter\nonpublic_dashboards_condition;
use mod_videotime\local\dash_framework\structure\videotime_table;

class videotime_stats_data_source extends abstract_data_source {

    /**
     * Constructor.
     *
     * @param context $context
     */
    public function __construct(context $context)  {
        $this->add_table(new videotime_table());
        parent::__construct($context);
    }

    /**
     * @return builder
     */
    public function get_query_template(): builder {
        $builder = new builder();
        $builder
            ->select('vt.id', 'vt_id')
            ->from('videotime', 'vt');

        return $builder;
    }

    /**
     * @return filter_collection_interface
     */
    public function build_filter_collection()
    {
        $filter_collection = new filter_collection(get_class($this), $this->get_context());

        $filter_collection->add_filter(new my_dashboards_condition('dd_id', 'dd.id'));

        $filter_collection->add_filter(new nonpublic_dashboards_condition('dd_nonpublic', 'dd.id'));

        return $filter_collection;
    }
}