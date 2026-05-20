/*
 * Show selected text track
 *
 * @package    mod_videotime
 * @module     mod_videotime/interaction
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Intialize listener for interaction
 */
export const initialize = () => {
    window.removeEventListener('interactionstart', showInteraction);
    window.addEventListener('interactionstart', showInteraction);
};

/**
 * Form change event handler
 *
 * @param {event} e mouse event
 */
const showInteraction = (e) => {
    alert(e.detail);
};
