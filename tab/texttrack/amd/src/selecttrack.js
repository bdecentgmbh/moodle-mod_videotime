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
 * Show selected text track
 *
 * @package    mod_videotime
 * @module     mod_videotime/selecttrrack
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define([], function() {

    var selectTrack = function() {
        this.initialize();
    };

    /**
     * Intialize listener and show current track
     */
    selectTrack.prototype.initialize = function() {
        var self = this;
        let url = new URL(window.location.href),
        lang = url.searchParams.get('lang');

        window.removeEventListener('change', self.handleChange.bind(this));
        document.querySelectorAll('form.videotimetab_texttrack_selectlang').forEach((form) => {
            if (lang) {
                form.querySelector('select option[value="' + lang + '"]').setAttribute('selected', true);
            }
            self.setLanguage(form);
        });
        window.addEventListener('change', self.handleChange.bind(this));
    };

    /**
     * Form change event handler
     *
     * @param {event} e mouse event
     */
    selectTrack.prototype.handleChange = function(event) {
        var self = this;
        let form = event.target.closest('form.videotimetab_texttrack_selectlang');
        if (form) {
            event.stopPropagation();
            event.preventDefault();
            self.setLanguage(form);
        }
    };

    /**
     * Show lang indicate by form
     *
     * @param {element} form
     */
    selectTrack.prototype.setLanguage = function(form) {
        let data = new FormData(form);
        form.closest('.tab-pane').querySelectorAll('.texttracks .row').forEach((row) => {
            if (row.getAttribute('data-lang') == data.get('lang')) {
                row.style.display = null;
            } else {
                row.style.display = 'none';
            }
        });
    };

    return {
        init : function() {
            return new selectTrack();
        }
    };
 });
