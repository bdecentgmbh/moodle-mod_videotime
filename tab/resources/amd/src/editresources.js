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
 * Opens an inline file-picker modal so teachers can manage resources without
 * leaving the activity page.
 *
 * @module     videotimetab_resources/editresources
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';

/**
 * Attach the click handler for the Edit button inside the resources tab.
 *
 * May be called multiple times (e.g. after the DOM is refreshed by a save),
 * so it always looks up the button freshly by data-action attribute.
 *
 * @param {number} cmid  Course-module id
 */
export const init = (cmid) => {
    const root = document.querySelector('.videotimetab_resources');
    if (!root) {
        return;
    }

    const btn = root.querySelector('[data-action="edit-resources"]');
    if (!btn) {
        return;
    }

    btn.addEventListener('click', async(e) => {
        e.preventDefault();

        const title = await getString('editresources', 'videotimetab_resources');

        const modal = new ModalForm({
            formClass: 'videotimetab_resources\\form\\editfiles_form',
            args: {cmid},
            modalConfig: {title},
        });

        modal.addEventListener(modal.events.FORM_SUBMITTED, (event) => {
            const {html} = event.detail;
            if (!html) {
                return;
            }

            // Replace the entire tab content with the freshly-rendered HTML
            // returned by process_dynamic_submission().
            const wrapper = document.querySelector('.videotimetab_resources');
            if (wrapper) {
                wrapper.outerHTML = html;
            }

            // Re-attach the listener on the new DOM node.
            init(cmid);
        });

        modal.show();
    });
};
