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
 * Custom comments interface
 *
 * @package    videotimetab_chat
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimetab_chat;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/comment/lib.php');

use cache;
use context;
use moodle_exception;
use stdClass;

/**
 * Custom comments interface
 *
 * @package    videotimetab_chat
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comment extends \comment {
    /** @var int itemid is used to associate with commenting content */
    private $itemid;

    /** @var int The context id for comments */
    private $contextid;

    /**
     * Construct function of comment class, initialise
     * class members
     *
     * @param stdClass $options {
     *            context => context context to use for the comment [required]
     *            component => string which plugin will comment being added to [required]
     *            itemid  => int the id of the associated item (forum post, glossary item etc) [required]
     *            area    => string comment area
     *            cm      => stdClass course module
     *            course  => course course object
     *            client_id => string an unique id to identify comment area
     *            autostart => boolean automatically expend comments
     *            showcount => boolean display the number of comments
     *            displaycancel => boolean display cancel button
     *            notoggle => boolean don't show/hide button
     *            linktext => string title of show/hide button
     * }
     */
    public function __construct(stdClass $options) {
        // Set context id.
        if (!empty($options->context)) {
            $this->contextid = $options->context->id;
        } else if (!empty($options->contextid)) {
            $this->contextid = $options->contextid;
        } else {
            throw new \moodle_exception('invalidcontext');
        }

        // Set item id.
        if (!empty($options->itemid)) {
            $this->itemid = $options->itemid;
        } else {
            $this->itemid = 0;
        }

        parent::__construct($options);
    }

    /**
     * Return matched comments
     *
     * @param  int $page
     * @param  str $sortdirection sort direction, ASC or DESC
     * @return array
     */
    public function get_comments($page = '', $sortdirection = 'ASC') {
        global $USER;

        $cache = cache::make('videotimetab_chat', 'comments');
        $cached = $cache->get($this->contextid . 'u' . $USER->id);
        if (!empty($cached) && $cache->get($this->contextid) <= $cached->timecreated) {
            return $cached->comments;
        }

        // Load all pages.
        $perpage = !empty($CFG->commentsperpage) ? $CFG->commentsperpage : 15;
        $count = $this->count();
        $pages = ceil($count / $perpage);
        $comments = [];
        for ($page = 0; $page < $pages; $page++) {
            $comments = array_merge($comments, parent::get_comments($page, $sortdirection));
        }

        $date = usergetmidnight(time());
        foreach ($comments as $c) {
            if ($date != usergetmidnight($c->timecreated)) {
                $date = usergetmidnight($c->timecreated);
                $c->date = userdate($date, get_string('strftimedate', 'langconfig'));
            }
            $c->strftimeformat = get_string('strftimetime', 'langconfig');
            $c->time = userdate($c->timecreated, $c->strftimeformat);
        }
        $cache->set(
            $this->contextid . 'u' . $USER->id,
            (object) [
                'timecreated' => time(),
                'comments' => $comments,
            ]
        );
        return $comments;
    }

    /**
     * Handle an event
     *
     * @param \core\event\base $event
     */
    public static function observe(\core\event\base $event) {
        // Update the cache.
        $eventdata = $event->get_data();
        $cache = cache::make('videotimetab_chat', 'comments');
        $cache->set($event->get_context()->id, time());

        // Send message to update clients.
        socket::observe($event);
    }
}
