/*
 * Show selected text track
 *
 * @package    mod_videotime
 * @module     mod_videotime/selecttrrack
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Intialize listener and show current track
 */
export const initialize = () => {
    let url = new URL(window.location.href),
        lang = url.searchParams.get('lang');

    window.removeEventListener('change', handleChange);
    document.querySelectorAll('form.videotimetab_texttrack_selectlang').forEach((form) => {
        if (lang) {
            form.querySelector('select option[value="' + lang + '"]').setAttribute('selected', true);
        }
        setLanguage(form);
    });
    window.addEventListener('change', handleChange);
};

/**
 * Form change event handler
 *
 * @param {event} e mouse event
 */
const handleChange = (e) => {
    let form = e.target.closest('form.videotimetab_texttrack_selectlang');
    if (form) {
        e.stopPropagation();
        e.preventDefault();
        setLanguage(form);
    }
};

/**
 * Show lang indicate by form
 *
 * @param {element} form
 */
const setLanguage = (form) => {
    let data = new FormData(form);
    form.closest('.tab-pane').querySelectorAll('.texttracks .row').forEach((row) => {
        if (row.getAttribute('data-lang') == data.get('lang')) {
            row.style.display = null;
        } else {
            row.style.display = 'none';
        }
    });
};
