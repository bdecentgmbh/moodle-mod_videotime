<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribdefault_view.phpute it and/or modify
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
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;

/**
 * Renderer for mod_videotime.
 *
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Overrides the parent so that templatable widgets are handled even without their explicit render method.
     *
     * @param renderable $widget
     * @return string
     * @throws \moodle_exception
     */
    public function render(renderable $widget) {

        $namespacedclassname = get_class($widget);
        $plainclassname = preg_replace('/^.*\\\/', '', $namespacedclassname);
        $rendermethod = 'render_'.$plainclassname;

        if (method_exists($this, $rendermethod)) {
            // Explicit rendering method exists, fall back to the default behaviour.
            return parent::render($widget);
        }

        $interfaces = class_implements($namespacedclassname);

        if (isset($interfaces['templatable'])) {
            // Default implementation of template-based rendering.
            $data = $widget->export_for_template($this);
            return parent::render_from_template('videotime/'.$plainclassname, $data);

        } else {
            return parent::render($widget);
        }
    }
}
