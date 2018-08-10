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
        init: function(cm, user, interval) {
            console.log("INIT");
            var player = new Vimeo('vimeo-embed', {
                responsive: 1
            });

            var playing = false;
            var time = 0;

            player.on('play', function() {
                playing = true;
            });
            player.on('playing', function() {
                playing = true;
            });
            player.on('timeupdate', function() {
                playing = true;
            });

            player.on('pause', function() {
                playing = false;
            });
            player.on('ended', function() {
                playing = false;
            });
            player.on('stalled', function() {
                playing = false;
            });
            player.on('suspend', function() {
                playing = false;
            });
            player.on('abort', function() {
                playing = false;
            });

            player.on('ended', function() {
                console.log('Finished.');
            });

            setInterval(function() {
                time += interval;
                if (playing) {
                    Ajax.call([{
                        methodname: 'mod_videotime_record_watch_time',
                        args: {user_id: user.id, module_id: cm.id, time: time}
                    }]);
                }
            }, interval * 1000);
        }
    };
});