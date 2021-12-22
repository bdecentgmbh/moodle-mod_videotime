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
    document.querySelectorAll('.instance-container, div.videotime-tab-instance').forEach(function (container) {
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
    document.querySelectorAll('.instance-container').forEach((container) => {
        if (!container.offsetWidth) {
            // Ignore if it is not visible.
            return;
        }
        document.querySelectorAll('.videotime-tab-instance .vimeo-embed iframe').forEach((iframe) => {
            let instance = iframe.closest('.videotime-tab-instance');
            Object.assign(instance.style, {
                top: container.offsetTop + 'px',
                left: container.offsetLeft + 'px',
                width: container.offsetWidth + 'px'
            });
            container.style.minHeight = iframe.closest('.videotime-tab-instance').offsetHeight + 'px';
            document.querySelectorAll('.videotime-tab-instance-cover').forEach((cover) => {
                Object.assign(cover.style, {
                    top: instance.style.top,
                    left: instance.style.left,
                    width: instance.style.width,
                    height: instance.offsetWidth + 'px'
                });
            });
        });
    });
};

/**
 * Reset handle when drag ends
 */
const dragendHandler = () => {
    document.querySelectorAll('.videotime-tab-instance-cover').forEach((cover) => {
        cover.style.display = 'none';
    });
};

/**
 * Prepare to drag divider
 *
 * @param {event} e mouse event
 */
const dragstartHandler = (e) => {
    if (e.target.classList.contains('videotimetab-resize-handle')) {
        e.stopPropagation();
        e.preventDefault();
        document.querySelectorAll('.videotime-tab-instance-cover').forEach((cover) => {
            cover.style.display = 'block';
        });
    }
};

/**
 * Resize the content and player to mouse location
 *
 * @param {event} e mouse event
 */
const mousemoveHandler = (e) => {
    document.querySelectorAll('.videotimetab-resize-handle').forEach((h) => {
        if (h.closest('.tab-pane') && document.querySelector('.videotime-tab-instance-cover').style.display == 'block') {
            let column = h.closest('.tab-pane').querySelector('.videotimetab-resize');
            column.style.width = e.pageX - column.getBoundingClientRect().left + 'px';
        }
    });
};
