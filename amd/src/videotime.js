/*
 * @package    mod_videotime
 * @copyright  2018 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module block_overview/helloworld
 */
define(['jquery', 'mod_videotime/player', 'core/ajax', 'core/log', 'core/templates'], function($, Vimeo, Ajax, log, Templates) {
    return {
        init: function(session, interval, hasPro, embedOptions, cmid, resumeTime, nextActivityUrl) {

            log.debug('VIDEO_TIME embed options', embedOptions);

            // Check if Vimeo video element exists.
            if ($('#vimeo-embed-' + cmid).length == 0) {
                log.debug("Vimeo video element not found: #vimeo-embed-" + cmid);
                return;
            }

            var player = new Vimeo('vimeo-embed-' + cmid, embedOptions);

            if (hasPro) {
                var playing = false;
                var time = 0;
                var percent = 0;
                var currentTime = 0;

                if (resumeTime > 0) {
                    player.on('loaded', function() {
                        player.setCurrentTime(resumeTime);
                    });
                }

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

                    // Ugly JavaScript chain.
                    // First set the state.
                    Ajax.call([{
                        methodname: 'videotimeplugin_pro_set_session_state',
                        args: {session_id: session.id, state: 1}
                    }])[0].done(function() {

                        // Then set the percentage.
                        Ajax.call([{
                            methodname: 'videotimeplugin_pro_set_percent',
                            args: {session_id: session.id, percent: 1}
                        }])[0].done(function() {

                            // Then set the watch time to the end.
                            Ajax.call([{
                                methodname: 'videotimeplugin_pro_set_session_current_time',
                                args: {session_id: session.id, current_time: currentTime}
                            }])[0].done(function() {

                                // Now that all data has been set on the session...
                                if (nextActivityUrl) {
                                    window.location.href = nextActivityUrl;
                                } else {
                                    Ajax.call([{
                                        methodname: 'videotimeplugin_pro_get_next_activity_button_data',
                                        args: {session_id: session.id}
                                    }])[0].done(function(response) {
                                        Templates.render('videotime/next_activity_button', JSON.parse(response.data))
                                            .then(function(html, js) {
                                                $('#next-activity-button').html(html);
                                            });
                                    });
                                }
                            });
                        });
                    });


                });

                player.on('timeupdate', function(event) {
                    percent = event.percent;
                    currentTime = event.seconds;
                    log.debug('VIDEO_TIME timeupdate. Percent: ' + percent + '. Current time: ' + currentTime);
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
                            Ajax.call([{
                                methodname: 'videotimeplugin_pro_set_session_current_time',
                                args: {session_id: session.id, current_time: currentTime}
                            }]);
                        }
                    }
                }, 1000);
            }
        }
    };
});
