/*
 * Position the Vimeo player within tab layout
 *
 * @package    mod_videotime
 * @module     mod_videotime/resize_tab_player
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Player from "mod_videotime/player";
import Notification from "core/notification";

let column;

/**
 * Intialize listener
 */
export const initialize = () => {
    let observer = new ResizeObserver(resize),
        mutationobserver = new MutationObserver(resize);
    mutationobserver.observe(document.body, {subtree: true, childList: true});
    document.querySelectorAll('.instance-container, div.videotime-tab-instance').forEach((container) => {
        observer.observe(container);
    });
    document.querySelectorAll('.videotime-tab-instance').forEach((instance) => {
        instance.style.position = 'absolute';
    });
    resize();

    window.removeEventListener('mousemove', mousemoveHandler);
    window.addEventListener('mousemove', mousemoveHandler);

    window.removeEventListener('dragstart', dragstartHandler);
    window.addEventListener('dragstart', dragstartHandler);

    window.removeEventListener('mouseup', dragendHandler);
    window.addEventListener('mouseup', dragendHandler);

    window.removeEventListener('click', cueVideo);
    window.addEventListener('click', cueVideo);
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
        container.closest('.videotimetabs').querySelectorAll('.videotime-tab-instance').forEach((instance) => {
            let content = container.closest('.videotimetabs').querySelector('.tab-content'),
                i = 0;
            Object.assign(instance.style, {
                top: container.offsetTop + 'px',
                left: container.offsetLeft + 'px',
                maxWidth: container.offsetWidth + 'px',
                width: container.offsetWidth + 'px'
            });
            container.closest('.videotimetabs').querySelectorAll('ul .nav-link').forEach(tab => {
                i++;
                if (tab.classList.contains('active')) {
                    instance.setAttribute('data-tab', i);
                }
            });
            container.style.minHeight = instance.offsetHeight + 5 + 'px';
            container.closest('.videotimetabs').querySelectorAll('.videotime-tab-instance-cover').forEach((cover) => {
                Object.assign(cover.style, {
                    height: content.offsetHeight + 'px',
                    left: content.offsetLeft + 'px',
                    top: content.offsetTop + 'px',
                    width: content.offsetWidth + 'px'
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
        column = e.target.closest('.tab-pane').querySelector('.videotimetab-resize');
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
            column.style.width = e.pageX - column.getBoundingClientRect().left + 'px';
        }
    });
};

/**
 * Move video to new time when link clicked
 *
 * @param {event} e mouse event
 */
const cueVideo = async(e) => {
    if (e.target.closest('[data-action="cue"]')) {
        const starttime = e.target.closest('a').getAttribute('data-start'),
            time = starttime.match(/((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+)?)/),
            iframe = e.target.closest('.videotimetabs').querySelector('.vimeo-embed iframe');
        if (iframe) {
            const player = new Player(iframe);
            e.preventDefault();
            e.stopPropagation();
            if (time) {
                try {
                    await player
                        .setCurrentTime(3600 * Number(time[3] || 0) + 60 * Number(time[5] || 0) + Number(time[6]));
                    player.play();
                } catch (e) {
                    Notification.exception(e);
                }
            }
        } else {
            const player = e.target.closest('.videotimetabs').querySelector('video');
            e.preventDefault();
            e.stopPropagation();
            player.currentTime = 3600 * Number(time[3] || 0) + 60 * Number(time[5] || 0) + Number(time[6]);
        }
    }
};
