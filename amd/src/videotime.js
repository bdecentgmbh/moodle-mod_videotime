/*
 * @package    mod_videotime
 * @copyright  2018 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module mod_videotime/videotime
 */
define(['jquery', 'mod_videotime/player', 'core/ajax', 'core/log', 'core/templates'], function($, Vimeo, Ajax, Log, Templates) {

    let VideoTime = function(elementId, cmId, hasPro, interval) {
        this.elementId = elementId;
        this.cmId = cmId;
        this.hasPro = hasPro;
        this.interval = interval;
        this.player = null;
        this.resumeTime = null;
        this.session = null;

        this.played = false;

        this.playing = false;
        this.time = 0;
        this.percent = 0;
        this.currentTime = 0;
        this.playbackRate = 1;
    };

    VideoTime.prototype.initialize = function() {
        Log.debug('Initializing Video Time ' + this.elementId);

        this.getEmbedOptions().then(function(response) {
            Log.debug('Initializing Vimeo player with options:');
            Log.debug(response);
            this.player = new Vimeo(this.elementId, JSON.parse(response.options));
            this.addListeners();
        }.bind(this));
    };

    /**
     * Register player events to respond to user interaction and play progress.
     */
    VideoTime.prototype.addListeners = function() {
        if (!this.player) {
            Log.debug('Player was not properly initialized for course module ' . this.cmId);
        }

        // Fire view event in Moodle on first play only.
        this.player.on('play', () => {
            if (!this.played) {
                if (this.hasPro) {
                    // Getting a new session on first play.
                    this.getSession().then(() => {
                        this.view();
                    });
                } else {
                    // Free version can still mark completion on video time view.
                    this.view();
                }
            }
        });

        // Features beyond this point are for pro only.
        if (!this.hasPro) {
            return;
        }

        // If resume is present force seek the player to that point.
        this.getResumeTime(this.cmId).then((seconds) => {
            Log.debug('VIDEO_TIME resuming at ' + seconds);
            if (seconds && seconds > 0) {
                this.player.on('loaded', () => {
                    this.player.setCurrentTime(seconds);
                });
            }
        });

        // Note: Vimeo player does not support multiple events in a single on() call. Each requires it's own function.

        // Catch all events where video plays.
        this.player.on('play', function (e) {
            this.playing = true;
            Log.debug('VIDEO_TIME play');
        }.bind(this));
        this.player.on('playing', function () {
            this.playing = true;
            Log.debug('VIDEO_TIME playing');
        }.bind(this));

        // Catch all events where video stops.
        this.player.on('pause', function () {
            this.playing = false;
            Log.debug('VIDEO_TIME pause');
        }.bind(this));
        this.player.on('stalled', function () {
            this.playing = false;
            Log.debug('VIDEO_TIME stalled');
        }.bind(this));
        this.player.on('suspend', function () {
            this.playing = false;
            Log.debug('VIDEO_TIME suspend');
        }.bind(this));
        this.player.on('abort', function () {
            this.playing = false;
            Log.debug('VIDEO_TIME abort');
        }.bind(this));

        this.player.getPlaybackRate().then(function(playbackRate) {
            this.playbackRate = playbackRate;
        }.bind(this));

        this.player.on('playbackratechange', function(event) {
            this.playbackRate = event.playbackRate;
        }.bind(this));

        // Always update internal values for percent and current time watched.
        this.player.on('timeupdate', function(event) {
            this.percent = event.percent;
            this.currentTime = event.seconds;
            Log.debug('VIDEO_TIME timeupdate. Percent: ' + this.percent + '. Current time: ' + this.currentTime);
        }.bind(this));

        // Initiate video finish procedure.
        this.player.on('ended', function () {
            this.playing = false;
            Log.debug('VIDEO_TIME ended');

            new Promise(function(resolve, reject) {
                this.getSession().then(function(session) {
                    resolve(session);
                });
            }.bind(this)).then(function(session) {
                this.setSessionState(session.id, 1);
                return session;
            }.bind(this)).then(function(session) {
                this.setPercent(session.id, 1);
                return session;
            }.bind(this)).then(function(session) {
                this.setCurrentTime(session.id, this.currentTime);
                return session;
            }.bind(this)).catch(function(error) {
                alert(error);
            }).finally(function(session) {
                this.getSession().then(function(session) {
                    this.getNextActivityButtonData(session.id).then(function(response) {
                        let data = JSON.parse(response.data);

                        if (parseInt(data.instance.next_activity_auto)) {
                            if (!data.is_restricted && data.hasnextcm) {
                                window.location.href = data.nextcm_url;
                            }
                        }

                        Templates.render('videotime/next_activity_button', JSON.parse(response.data))
                            .then(function (html, js) {
                                $('#next-activity-button').html(html);
                            });
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        }.bind(this));

        this.startWatchInterval();
    };

    /**
     * Start interval that will periodically record user progress via Ajax.
     */
    VideoTime.prototype.startWatchInterval = function() {
        setInterval(function () {
            if (this.playing) {
                this.time += this.playbackRate;

                this.getSession().then(function(session) {
                    if (this.time % this.interval === 0) {
                        Log.debug('VIDEO_TIME watch_time: ' + this.time + '. percent: ' + this.percent);
                        this.recordWatchTime(session.id, this.time);
                        this.setPercent(session.id, this.percent);
                        this.setCurrentTime(session.id, this.currentTime);
                    }
                }.bind(this));
            }
        }.bind(this), 1000);
    };

    /**
     * Set state on session.
     *
     * @param sessionId
     * @param state
     * @returns {Promise}
     */
    VideoTime.prototype.setSessionState = function(sessionId, state) {
        return Ajax.call([{
            methodname: 'videotimeplugin_pro_set_session_state',
            args: {session_id: sessionId, state: state}
        }])[0];
    };

    /**
     * Set current watch time for video. Used for resuming.
     *
     * @param {int} sessionId
     * @param {float} currentTime
     * @returns {Promise}
     */
    VideoTime.prototype.setCurrentTime = function(sessionId, currentTime) {
        return Ajax.call([{
            methodname: 'videotimeplugin_pro_set_session_current_time',
            args: {session_id: sessionId, current_time: currentTime}
        }])[0];
    };

    /**
     * Set video watch percentage for session.
     *
     * @param {int} sessionId
     * @param {float} percent
     * @returns {Promise}
     */
    VideoTime.prototype.setPercent = function(sessionId, percent) {
        return Ajax.call([{
            methodname: 'videotimeplugin_pro_set_percent',
            args: {session_id: sessionId, percent: percent}
        }])[0];
    };

    /**
     * Record watch time for session.
     *
     * @param {int} sessionId
     * @param {float} time
     * @returns {Promise}
     */
    VideoTime.prototype.recordWatchTime = function(sessionId, time) {
        return Ajax.call([{
            methodname: 'videotimeplugin_pro_record_watch_time',
            args: {session_id: sessionId, time: time}
        }])[0];
    };

    /**
     * Get data for next activity button.
     *
     * @param {int} sessionId
     * @returns {Promise}
     */
    VideoTime.prototype.getNextActivityButtonData = function(sessionId) {
        return Ajax.call([{
            methodname: 'videotimeplugin_pro_get_next_activity_button_data',
            args: { session_id: sessionId }
        }])[0];
    };

    /**
     * Get embed options for this course module.
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getEmbedOptions = function() {
        return Ajax.call([{
            methodname: 'mod_videotime_get_embed_options',
            args: { cmid: this.cmId }
        }])[0];
    };

    /**
     * Get time to resume video as seconds.
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getResumeTime = function(cmId) {
        if (this.resumeTime) {
            return Promise.resolve(this.resumeTime);
        }

        return new Promise((resolve, reject) => {
            Ajax.call([{
                methodname: 'videotimeplugin_pro_get_resume_time',
                args: { cmid: cmId }
            }])[0].then(function(response) {
                this.resumeTime = response.seconds;
                resolve(this.resumeTime);
            }.bind(this));
        });
    };

    /**
     * Get new or existing video viewing session.
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getSession = function() {
        if (this.session) {
            return Promise.resolve(this.session);
        }

        return new Promise((resolve, reject) => {
            Ajax.call([{
                methodname: 'videotimeplugin_pro_get_new_session',
                args: { cmid: this.cmId }
            }])[0].then(function(response) {
                this.session = response;
                resolve(response);
            }.bind(this));
        });
    };

    /**
     * Log the user view of video.
     *
     * @returns {Promise}
     */
    VideoTime.prototype.view = function() {
        return Ajax.call([{
            methodname: 'mod_videotime_view_videotime',
            args: { cmid: this.cmId }
        }])[0];
    };

    return VideoTime;
});
