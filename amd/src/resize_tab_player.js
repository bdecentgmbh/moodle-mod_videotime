/*
 * Position the Vimeo player within tab layout
 *
 * @package    mod_videotime
 * @module     mod_videotime/resize_tab_player
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module mod_videotime/resize_tab_player
 */
 define(['mod_videotime/player', 'core/notification'], function(Player, Notification) {

    var resizeTabPlayer = function() {
        this.initialize();
    };

    resizeTabPlayer.prototype.column = '';

    /**
     * Intialize listener
     */
    resizeTabPlayer.prototype.initialize = function() {
        var self = this;
        let observer = new ResizeObserver(self.resize),
        mutationobserver = new MutationObserver(self.resize);
        mutationobserver.observe(document.querySelector('#page-content'), {subtree: true, childList: true});
        document.querySelectorAll('.instance-container, div.videotime-tab-instance').forEach((container) => {
            observer.observe(container);
        });
        self.resize();
        document.querySelectorAll('.videotime-tab-instance').forEach((instance) => {
            instance.style.position = 'absolute';
        });

        window.removeEventListener('mousemove', self.mousemoveHandler);
        window.addEventListener('mousemove', self.mousemoveHandler);

        window.removeEventListener('dragstart', self.dragstartHandler);
        window.addEventListener('dragstart', self.dragstartHandler);

        window.removeEventListener('mouseup', self.dragendHandler);
        window.addEventListener('mouseup', self.dragendHandler);

        window.removeEventListener('click', self.cueVideo);
        window.addEventListener('click', self.cueVideo);
    };

    /**
     * Adjust player position when the page configuration is changed
     */
    resizeTabPlayer.prototype.resize = function() {
        document.querySelectorAll('.instance-container').forEach((container) => {
            if (!container.offsetWidth) {
                // Ignore if it is not visible.
                return;
            }
            container.closest('.videotimetabs').querySelectorAll('.videotime-tab-instance .vimeo-embed iframe')
                .forEach((iframe) => {
            let instance = iframe.closest('.videotime-tab-instance'),
                content = iframe.closest('.tab-content');
                Object.assign(instance.style, {
                    top: container.offsetTop + 'px',
                    left: container.offsetLeft + 'px',
                    width: container.offsetWidth + 'px'
                });
                container.style.minHeight = iframe.closest('.videotime-tab-instance').offsetHeight + 'px';
                content.querySelectorAll('.videotime-tab-instance-cover').forEach((cover) => {
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
    resizeTabPlayer.prototype.dragendHandler = function() {
        document.querySelectorAll('.videotime-tab-instance-cover').forEach((cover) => {
            cover.style.display = 'none';
        });
    };

    /**
     * Prepare to drag divider
     *
     * @param {event} e mouse event
     */
    resizeTabPlayer.prototype.dragstartHandler = function(e) {
        if (e.target.classList.contains('videotimetab-resize-handle')) {
            this.column = e.target.closest('.tab-pane').querySelector('.videotimetab-resize');
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
    resizeTabPlayer.prototype.mousemoveHandler = function(e) {
        document.querySelectorAll('.videotimetab-resize-handle').forEach((h) => {
            if (h.closest('.tab-pane') && document.querySelector('.videotime-tab-instance-cover').style.display == 'block') {
                this.column.style.width = e.pageX - this.column.getBoundingClientRect().left + 'px';
            }
        });
    };

    /**
     * Move video to new time when link clicked
     *
     * @param {event} e mouse event
     */
    resizeTabPlayer.prototype.cueVideo = function(e) {
        if (e.target.matches('[data-action="cue"]')) {
            let starttime = e.target.closest('a').getAttribute('data-start'),
                time = starttime.match(/((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/),
                iframe = e.target.closest('.videotimetabs').querySelector('.vimeo-embed iframe'),
                player = new Player(iframe);
            e.preventDefault();
            e.stopPropagation();
            if (time) {
                player
                .setCurrentTime(3600 * Number(time[3] || 0) + 60 * Number(time[5] || 0) + Number(time[6]))
                .then(player.play.bind(player))
                .catch(Notification.exception);
            }
        }
    };

    return {
        init: function() {
            return new resizeTabPlayer();
        }
    };
});
