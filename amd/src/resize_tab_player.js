/*
 * Position the Vimeo player within tab layout
 *
 * @package    mod_videotime
 * @module     mod_videotime/resize_tab_player
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Intialize listener
 */
export const initialize = () => {
    var observer = new ResizeObserver(resize);
    document.querySelectorAll('.tab-pane .instance-container, div.videotime-tab-instance').forEach(function (container) {
        observer.observe(container);
    }.bind(this));
    resize();
    document.querySelectorAll('.videotime-tab-instance').forEach((instance) => {
        instance.style.position = 'absolute';
    });

    window.removeEventListener('mousemove', mousemoveHandler);
    window.addEventListener('mousemove', mousemoveHandler);

    window.removeEventListener('dragstart', dragstartHandler);
    window.addEventListener('dragstart', dragstartHandler);

    window.removeEventListener('mouseup', dragendHandler);
    window.addEventListener('mouseup', dragendHandler);
};

/**
 * Adjust player position when the page configuration is changed
 */
const resize = () => {
    document.querySelectorAll('.tab-pane.active .instance-container').forEach((container) => {
        document.querySelectorAll('.vimeo-embed iframe').forEach((iframe) => {
            iframe.width = container.offsetWidth - 10;
            document.querySelectorAll('.tab-pane').forEach((pane) => {
                pane.style.minHeight =  iframe.parentNode.parentNode.offsetHeight + 20 + 'px';
            });
            Object.assign(iframe.closest('.videotime-tab-instance').style, {
                top: container.offsetTop + 'px',
                left: container.offsetLeft + 'px',
                width: container.offsetWidth + 'px'
            });
            container.style.minHeight = iframe.closest('.videotime-tab-instance').offsetHeight + 'px';
        });
    });
};

/**
 * Reset handle when drag ends
 */
const dragendHandler = () => {
    document.querySelectorAll('.videotimetab-resize-handle').forEach((h) => {
        h.style.position = 'relative';
    });
};

/**
 * Prepare to drag border
 *
 * @param {event} e mouse event
 */
const dragstartHandler = (e) => {
    if (e.target.classList.contains('videotimetab-resize-handle')) {
        e.stopPropagation();
        e.preventDefault();
        e.target.style.position = 'absolute';
    }
};

/**
 * Resize the content and player to mouse location
 *
 * @param {event} e mouse event
 */
const mousemoveHandler = (e) => {
    document.querySelectorAll('.videotimetab-resize-handle').forEach((h) => {
        if (h.style.position === 'absolute') {
            let column = h.closest('.tab-pane').querySelector('.videotimetab-resize');
            column.style.width = e.pageX - column.getBoundingClientRect().left + 'px';
        }
    });
};
