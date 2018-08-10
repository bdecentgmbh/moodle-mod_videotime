/*
 * @package    mod_videotime
 * @copyright  2018 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module block_overview/helloworld
 */
define(['jquery', 'mod_videotime/player', 'core/ajax'], function($, Vimeo, Ajax) {
    return {
        init: function(moduleId, userId) {
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

            setInterval(function() {
                Ajax.call([{
                    methodname: 'mod_videotime_record_watch_time',
                    args: { user_id: userId, module_id: moduleId }
                }]);
            }, 5000);
        }
    };
});