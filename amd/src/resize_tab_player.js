/*
 * @package    mod_videotime
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module mod_videotime/resize_tab_player
 */
define([
    'jquery',
], function(
    $
) {
    let Resizer = function() {
    };

    Resizer.prototype.init = function() {};
    Resizer.prototype.initialize = function() {
        var observer = new ResizeObserver(this.resize);
        $('.tab-pane .instance-container, div.videotime-tab-instance').each(function(index, container) {
            observer.observe(container);
        }.bind(this));
        this.resize();
    };

    Resizer.prototype.resize = function() {
        $('.tab-pane.active .instance-container').each(function(index, container) {
            $('.vimeo-embed iframe').attr('width', $(container));
            $('.vimeo-embed iframe').each(function() {
                this.width = container.offsetWidth - 10;
                $(container).css('height', this.parentNode.offsetHeight + 20 + 'px');
                $('.tab-pane').css('min-height', this.parentNode.parentNode.offsetHeight + 20 + 'px');
                $(this).closest('.videotime-tab-instance').css('top', $(container).position().top + 'px');
                $(this).closest('.videotime-tab-instance').css('left', $(container).position().left + 'px');
                $(this).closest('.videotime-tab-instance').css('width', container.offsetWidth + 'px');
            });
        });
    };

    return new Resizer();
});
