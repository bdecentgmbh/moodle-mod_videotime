/*
 * @package    mod_videotime
 * @copyright  2018 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module block_overview/helloworld
 */
define(['jquery', 'mod_videotime/player', 'core/ajax', 'core/log'], function($, Vimeo, Ajax, log) {
    return {
        init: function(session, interval, hasPro, embedOptions) {

            log.debug('VIDEO_TIME embed options', embedOptions);

            var player = new Vimeo('vimeo-embed', embedOptions);

            if (hasPro) {
                var playing = false;
                var time = 0;
                var percent = 0;

                player.on('play', function () {
                    playing = true;
                    log.debug('VIDEO_TIME play');
                });
                player.on('playing', function () {
                    playing = true;
                    log.debug('VIDEO_TIME playing');
                });

                player.on('pause', function () {
                    playing = false;
                    log.debug('VIDEO_TIME pause');
                });
                player.on('stalled', function () {
                    playing = false;
                    log.debug('VIDEO_TIME stalled');
                });
                player.on('suspend', function () {
                    playing = false;
                    log.debug('VIDEO_TIME suspend');
                });
                player.on('abort', function () {
                    playing = false;
                    log.debug('VIDEO_TIME abort');
                });

                player.on('ended', function () {
                    playing = false;
                    log.debug('VIDEO_TIME ended');
                    Ajax.call([{
                        methodname: 'videotimeplugin_pro_set_session_state',
                        args: {session_id: session.id, state: 1}
                    }]);
                    Ajax.call([{
                        methodname: 'videotimeplugin_pro_set_percent',
                        args: {session_id: session.id, percent: 1}
                    }]);
                });

                player.on('timeupdate', function(event) {
                    percent = event.percent;
                    log.debug('VIDEO_TIME timeupdate');
                });

                setInterval(function () {
                    if (playing) {
                        time++;

                        if (time % interval === 0) {
                            log.debug('VIDEO_TIME watch_time: ' + time + '. percent: ' + percent);
                            Ajax.call([{
                                methodname: 'videotimeplugin_pro_record_watch_time',
                                args: {session_id: session.id, time: time}
                            }]);
                            Ajax.call([{
                                methodname: 'videotimeplugin_pro_set_percent',
                                args: {session_id: session.id, percent: percent}
                            }]);
                        }
                    }
                }, 1000);
            }
        }
    };
});