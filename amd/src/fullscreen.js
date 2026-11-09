/*
 * Expand activity to full screen display
 *
 * @package    videotimeplugin_pro
 * @module     videotimeplugin_pro/fullscreen
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from "core/notification";
//import Templates from "core/templates";

export default {
    initialize: function() {
        const container = document.querySelector('.videotimetabs') || document.querySelector('.videotime-embed, vimeo-embed');
        const button = document.querySelector('#fullscreen-button button');
        button.addEventListener('click', e => {
            try {
                container.requestFullscreen();
                e.preventDefault();
            } catch (e) {
                Notification.exception(e);
            }
        });
    }
};
