/*
 * Audio control listner
 *
 * @package    videotimeplugin_live
 * @module     videotimeplugin_live/audioswitch
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handle click event
 *
 * @param {Event} e Click event
 */
const handleClick = e => {
    const button = e.target.closest('[data-contextid] button[data-audio]');

    if (button) {
        const action = button.getAttribute('data-audio'),
            contextid = button.closest('[data-contextid]').getAttribute('data-contextid');

        e.stopPropagation();
        e.preventDefault();

        document.querySelectorAll('[data-contextid] button[data-audio]').forEach(button => {
            if (button.getAttribute('data-audio') === action) {
                button.classList.add('hidden');
            } else {
                button.classList.remove('hidden');
            }
        });

        document.querySelectorAll(`[data-contextid="${contextid}"] audio`).forEach(audio => {
            audio.setAttribute('volume', action === 'disable' ? 0 : 1);
            audio.muted = (action === 'disable');
        });
    }
};

export default {
    /**
     * Add listener
     */
    init: function() {
        document.removeEventListener('click', handleClick);
        document.addEventListener('click', handleClick);
    }
};
