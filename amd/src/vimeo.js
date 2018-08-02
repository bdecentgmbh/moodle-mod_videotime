// Standard license block omitted.
/*
 * @package    block_overview
 * @copyright  2015 Someone cool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module block_overview/helloworld
 */
define(['jquery', 'mod_vimeo/player'], function($, Vimeo) {
    return {
        init: function() {
            console.log("INIT");
            var player = new Vimeo('vimeo-embed', {
                responsive: 1
            });

            player.on('ended', function() {
                console.log('Finished.');
            });

            player.on('seeked', function() {
                console.log('Seek.');
            });
            player.on('timeupdate', function() {
                console.log('timeupdate.');
            });
        }
    };
});