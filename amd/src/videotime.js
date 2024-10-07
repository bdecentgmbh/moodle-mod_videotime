/*
 * @package    mod_videotime
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module mod_videotime/videotime
 */
define([
    'jquery',
    'mod_videotime/player',
    'core/ajax',
    'core/config',
    'core/log',
    'core/templates',
    'core/notification'
], function($, Vimeo, Ajax, Config, Log, Templates, Notification) {
    let VideoTime = function(elementId, cmId, hasPro, interval, instance) {
        this.elementId = elementId;
        this.cmId = cmId;
        this.hasPro = hasPro;
        this.interval = interval;
        this.player = null;
        this.resumeTime = null;
        this.session = null;
        this.instance = instance;

        this.played = false;

        this.playing = false;
        this.time = 0;
        this.percent = 0;
        this.currentTime = 0;
        this.playbackRate = 1;

        this.plugins = [];

        if (hasPro && $('body').hasClass('path-course-view') && !$('body').hasClass('vtinit')) {
            $('body').addClass('vtinit');
        }
        this.modulecount = $('body .activity.videotime').length;
    };

    /**
     * Get course module ID of this VideoTime instance.
     *
     * @return {int}
     */
    VideoTime.prototype.getCmId = function() {
        return this.cmId;
    };

    /**
     * Register a plugin to hook into VideoTime functionality.
     *
     * @param {VideoTimePlugin} plugin
     */
    VideoTime.prototype.registerPlugin = function(plugin) {
        this.plugins.push(plugin);
    };

    VideoTime.prototype.initialize = function() {
        let instance = this.instance;
        Log.debug('Initializing Video Time ' + this.elementId);

        Log.debug('Initializing Vimeo player with options:');
        Log.debug(instance);
        this.player = new Vimeo(this.elementId, {
            autopause: Number(instance.autopause),
            autoplay: Number(instance.autoplay),
            background: Number(instance.background),
            byline: Number(instance.byline),
            color: instance.color,
            controls: Number(instance.controls),
            dnt: Number(instance.dnt),
            height: instance.height,
            loop: Number(instance.option_loop),
            maxheight: instance.maxheight,
            maxwidth: instance.maxwidth,
            muted: Number(instance.muted),
            portrait: instance.portrait,
            pip: Number(instance.pip),
            playsinline: instance.playsinline,
            responsive: Number(instance.responsive),
            speed: instance.speed,
            title: Number(instance.title),
            transparent: Number(instance.transparent),
            url: instance.vimeo_url,
            width: instance.width
        });

        this.handleStartTime();

        this.addListeners();

        for (let i = 0; i < this.plugins.length; i++) {
            const plugin = this.plugins[i];
            plugin.initialize(this, instance);
        }

        return true;
    };

    VideoTime.prototype.handleStartTime = async function() {
        const url = new URL(window.location.href),
            q = url.searchParams.get('q'),
            starttime = (url.searchParams.get('time') || '').match(/^([0-9]+:){0,2}([0-9]+)(\.[0-9]+)$/);
        if (starttime) {
            await this.setStartTime(starttime[0]);
        }
        if (q && window.find) {
            window.find(q);
        }
    };

    /**
     * Get pause state
     *
     * @return {bool}
     */
    VideoTime.prototype.getPaused = async function() {
        return await this.player.getPaused();
    };

    /**
     * Get Vimeo player object.
     *
     * @returns {Vimeo}
     */
    VideoTime.prototype.getPlayer = function() {
        return this.player;
    };

    /**
     * Register player events to respond to user interaction and play progress.
     */
    VideoTime.prototype.addListeners = async function() {
        if (!this.player) {
            Log.debug('Player was not properly initialized for course module ' + this.cmId);
            return;
        }

        // If this is a tab play set time cues and listener.
        $($('#' + this.elementId).closest('.videotimetabs')).each(function(i, tabs) {
           $(tabs).find('[data-action="cue"]').each(async function(index, anchor) {
                let starttime = anchor.getAttribute('data-start'),
                    time = starttime.match(/((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/);
                if (time) {
                    await this.player.addCuePoint(
                        3600 * Number(time[3] || 0) + 60 * Number(time[5] || 0) + Number(time[6]),
                        {
                            starttime: starttime
                        }
                    );
                }
            }.bind(this));

            this.player.on('cuepoint', function(event) {
                if (event.data.starttime) {
                    $(tabs).find('.videotime-highlight').removeClass('videotime-highlight');
                    $(tabs).find('[data-action="cue"][data-start="' + event.data.starttime + '"]')
                        .closest('.row')
                        .addClass('videotime-highlight');
                    $('.videotime-highlight').each(function() {
                        if (this.offsetTop) {
                            this.parentNode.scrollTo({
                                top: this.offsetTop - 50,
                                left: 0,
                                behavior: 'smooth'
                            });
                        }
                    });
                }
            });
        }.bind(this));

        // Fire view event in Moodle on first play only.
        this.player.on('play', () => {
            if (this.hasPro) {
                this.startWatchInterval();
            }
            this.view();
            return true;
        });

        // Features beyond this point are for pro only.
        if (!this.hasPro) {
            return;
        }

        // If resume is present force seek the player to that point.
        this.player.on("loaded", () => {
            if (!this.instance.resume_playback || !this.instance.resume_time || this.instance.resume_time <= 0) {
                return;
            }

            this.resume();
        });

        // Note: Vimeo player does not support multiple events in a single on() call. Each requires it's own function.

        // Catch all events where video plays.
        this.player.on('play', function() {
            this.playing = true;
            Log.debug('VIDEO_TIME play');
        }.bind(this));
        this.player.on('playing', function() {
            this.playing = true;
            Log.debug('VIDEO_TIME playing');
        }.bind(this));

        // Catch all events where video stops.
        this.player.on('pause', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME pause');
        }.bind(this));
        this.player.on('stalled', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME stalled');
        }.bind(this));
        this.player.on('suspend', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME suspend');
        }.bind(this));
        this.player.on('abort', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME abort');
        }.bind(this));

        this.playbackRate = await this.getPlaybackRate();

        this.player.on('playbackratechange', function(event) {
            this.playbackRate = event.playbackRate;
        }.bind(this));

        // Always update internal values for percent and current time watched.
        this.player.on('timeupdate', function(event) {
            setTimeout(async() => {
                var preventUpdate = false;
                if (event.seconds === event.duration) {
                    this.plugins.forEach(async(plugin) => {
                        if (typeof plugin.setCurrentTime == 'function') {
                            const session = plugin.getSession();
                            plugin.setCurrentTime(session.id, event.seconds);
                        }
                    });
                }
                if (Number(this.instance.preventfastforwarding)) {
                    const playbackRate = await this.getPlaybackRate();
                    this.plugins.forEach((plugin) => {
                        if (
                                (typeof plugin.watchTime != 'undefined')
                                && (event.seconds > plugin.watchTime + plugin.fastForwardBuffer * playbackRate)
                        ) {
                            preventUpdate = true;
                        }
                    });
                }
                if (preventUpdate) {
                    return;
                }
                this.percent = event.percent;
                this.currentTime = event.seconds;
            }, 100);
        }.bind(this));

        // Initiate video finish procedure.
        this.player.on('ended', this.handleEnd.bind(this));
        this.player.on('pause', this.handlePause.bind(this));
    };

    VideoTime.prototype.resume = async function() {
        const duration = await this.getDuration();
        let resumeTime = this.instance.resume_time;
        // Duration is often a little greater than a resume time at the end of the video.
        // A user may have watched 100 seconds when the video ends, but the duration may be
        // 100.56 seconds. BUT, sometimes the duration is rounded depending on when the
        // video loads, so it may be 101 seconds. Hence the +1 and Math.floor usage.
        if (resumeTime + 1 >= Math.floor(duration)) {
            Log.debug(
                "VIDEO_TIME video finished, resuming at start of video."
            );
            resumeTime = 0;
        }
        Log.debug("VIDEO_TIME duration is " + duration);
        Log.debug("VIDEO_TIME resuming at " + resumeTime);
        await this.setCurrentPosition(resumeTime);
        return true;
    };

    /**
     * Handle pause
     */
    VideoTime.prototype.handlePause = function() {
        this.plugins.forEach(plugin => {
            if (typeof plugin.handlePause == 'function') {
                plugin.handlePause();
            }
        });
    };

    /**
     * Start interval that will periodically record user progress via Ajax.
     */
    VideoTime.prototype.handleEnd = async function() {
        this.playing = false;
        Log.debug('VIDEO_TIME ended');
        if (this.plugins.length > 2) {
            this.plugins.forEach(plugin => {
                if (typeof plugin.handleEnd == 'function') {
                    plugin.handleEnd();
                }
            });
        } else {
            // This moved to pro plugin, but left for compatibility.
            const session = await this.getSession();
            await this.setSessionState(session.id, 1);
            await this.setPercent(session.id, 1);
            await this.setCurrentTime(session.id, this.currentTime);
            const response = await this.getNextActivityButtonData(session.id);
            const data = JSON.parse(response.data);
            if (data.instance && parseInt(data.instance.next_activity_auto)) {
                if (!data.is_restricted && data.hasnextcm) {
                    let link = $('.aalink[href="' + data.nextcm_url + '"] img').first();
                    if ($('.path-course-view').length && link) {
                        link.click();
                    } else {
                        window.location.href = data.nextcm_url;
                    }
                }
            }

            const html = await Templates.render('videotime/next_activity_button', JSON.parse(response.data));
            $('#next-activity-button').html(html);
        }
    };

    /**
     * Start interval that will periodically record user progress via Ajax.
     */
    VideoTime.prototype.startWatchInterval = function() {
        this.plugins.forEach(plugin => {
            if (typeof plugin.startWatchInterval == 'function') {
                this.watchInterval = true;
                plugin.startWatchInterval();
            }
        });
        if (this.watchInterval) {
            return;
        }

        this.watchInterval = setInterval(async function() {
            if (!this.getPaused()) {
                this.time += this.playbackRate;
            }
        }.bind(this), 1000);

        this.watchInterval = setInterval(async function() {
            const session = await this.getSession();
            Log.debug('VIDEO_TIME watch_time: ' + this.time + '. percent: ' + this.percent);
            this.recordWatchTime(session.id, this.time);
            this.setPercent(session.id, this.percent);
            this.setCurrentTime(session.id, this.currentTime);
        }.bind(this), this.interval);
    };

    /**
     * Set state on session.
     *
     * @param {int} sessionId
     * @param {int} state
     * @returns {Promise}
     */
    VideoTime.prototype.setSessionState = async function(sessionId, state) {
        if (this.instance.token) {
            const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                data = url.searchParams;
            data.set('wstoken', this.instance.token);
            data.set('moodlewsrestformat', 'json');
            data.set('wsfunction', 'videotimeplugin_pro_set_session_state');
            data.set('state', state);
            data.set('session_id', sessionId);
            const response = await fetch(url);
            if (!response.ok) {
                Notification.exeption('Web service error');
            }
            return await response.json();
        }

        return await Ajax.call([{
            methodname: 'videotimeplugin_pro_set_session_state',
            args: {"session_id": sessionId, state: state},
            fail: Notification.exception
        }])[0];
    };

    /**
     * Set current watch time for video. Used for resuming.
     *
     * @param {int} sessionId
     * @param {float} currentTime
     * @returns {Promise}
     */
    VideoTime.prototype.setCurrentTime = async function(sessionId, currentTime) {
        if (this.instance.token) {
            const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                data = url.searchParams;
            data.set('wstoken', this.instance.token);
            data.set('moodlewsrestformat', 'json');
            data.set('wsfunction', 'videotimeplugin_pro_set_session_current_time');
            data.set('current_time', currentTime);
            data.set('session_id', sessionId);
            const response = await fetch(url);
            if (!response.ok) {
                Notification.exeption('Web service error');
            }
            return await response.json();
        }
        return await Ajax.call([{
            methodname: 'videotimeplugin_pro_set_session_current_time',
            args: {"session_id": sessionId, "current_time": currentTime},
            fail: Notification.exception
        }])[0];
    };

    /**
     * Set video watch percentage for session.
     *
     * @param {int} sessionId
     * @param {float} percent
     * @returns {Promise}
     */
    VideoTime.prototype.setPercent = async function(sessionId, percent) {
        if (this.instance.token) {
            const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                data = url.searchParams;
            data.set('wstoken', this.instance.token);
            data.set('moodlewsrestformat', 'json');
            data.set('wsfunction', 'videotimeplugin_pro_set_percent');
            data.set('percent', percent);
            data.set('session_id', sessionId);
            const response = await fetch(url);
            if (!response.ok) {
                Notification.exeption('Web service error');
            }
            return await response.json();
        }
        return await Ajax.call([{
            methodname: 'videotimeplugin_pro_set_percent',
            args: {"session_id": sessionId, percent: percent},
            fail: Notification.exception
        }])[0];
    };

    /**
     * Record watch time for session.
     *
     * @param {int} sessionId
     * @param {float} time
     * @returns {Promise}
     */
    VideoTime.prototype.recordWatchTime = async function(sessionId, time) {
        if (this.instance.token) {
            const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                data = url.searchParams;
            data.set('wstoken', this.instance.token);
            data.set('moodlewsrestformat', 'json');
            data.set('wsfunction', 'videotimeplugin_pro_record_watch_time');
            data.set('session_id', sessionId);
            data.set('time', time);
            const response = await fetch(url);
            if (!response.ok) {
                Notification.exeption('Web service error');
            }
            return await response.json();
        }
        return await Ajax.call([{
            methodname: 'videotimeplugin_pro_record_watch_time',
            args: {"session_id": sessionId, time: time},
            fail: Notification.exception
        }])[0];
    };

    /**
     * Get data for next activity button.
     *
     * @param {int} sessionId
     * @returns {Promise}
     */
    VideoTime.prototype.getNextActivityButtonData = async function(sessionId) {
        if (this.instance.token) {
            // We do not support button in iframe.
            return {data: '{}'};
        }
        return await Ajax.call([{
            methodname: 'videotimeplugin_pro_get_next_activity_button_data',
            args: {"session_id": sessionId}
        }])[0];
    };

    /**
     * Get VideoTime instance for this course module.
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getInstance = async function() {
        if (this.instance) {
            return this.instance;
        }

        return await Ajax.call([{
            methodname: 'mod_videotime_get_videotime',
            args: {cmid: this.cmId},
            done: (response) => {
                this.instance = response;
                return this.instance;
            },
            fail: Notification.exception
        }])[0];
    };

    /**
     * Get time to resume video as seconds.
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getResumeTime = async function() {
        if (this.resumeTime) {
            return this.resumeTime;
        }

        return await Ajax.call([{
            methodname: 'videotimeplugin_pro_get_resume_time',
            args: {cmid: this.cmId},
            done: (response) => {
                this.resumeTime = response.seconds;
                return this.resumeTime;
            },
            fail: Notification.exception
        }])[0];
    };

    /**
     * Get new or existing video viewing session.
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getSession = async function() {
        if (this.instance.token) {
            if (!this.session) {
                const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                    data = url.searchParams;
                data.set('wstoken', this.instance.token);
                data.set('moodlewsrestformat', 'json');
                data.set('wsfunction', 'videotimeplugin_pro_get_new_session');
                data.set('cmid', this.cmId);
                this.session = await fetch(url);
                if (!this.session.ok) {
                    Notification.exeption('Web service error');
                }
                return await this.session.json();
            }

            return await this.session;
        }
        if (!this.session) {
            this.session = Ajax.call([{
                methodname: 'videotimeplugin_pro_get_new_session',
                args: {cmid: this.cmId},
                fail: Notification.exception
            }])[0];
        }
        return await this.session;
    };

    /**
     * Parse start time and set player
     *
     * @param {string} starttime
     * @returns {Promise}
     */
    VideoTime.prototype.setStartTime = async function(starttime) {
        const time = starttime.match(/((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/);
        if (time) {
            this.resumeTime = 3600 * Number(time[3] || 0) + 60 * Number(time[5] || 0) + Number(time[6]);
            this.currentTime(this.resumeTime);
        }
        return await this.player.getCurrentTime();
    };

    /**
     * Log the user view of video.
     *
     * @returns {Promise}
     */
    VideoTime.prototype.view = async function() {
        if (this.instance.token) {
            const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                data = url.searchParams;
            data.set('wstoken', this.instance.token);
            data.set('moodlewsrestformat', 'json');
            data.set('wsfunction', 'mod_videotime_view_videotime');
            data.set('cmid', this.cmId);
            const response = await fetch(url);
            if (!response.ok) {
                Notification.exeption('Web service error');
            }
            return await response.json();
        }
        return await Ajax.call([{
            methodname: 'mod_videotime_view_videotime',
            args: {cmid: this.cmId},
            fail: Notification.exception
        }])[0];
    };

    /**
     * Get play back rate
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getPlaybackRate = async function() {
        try {
            const playbackRate = await this.player.getPlaybackRate();
            return playbackRate;
        } catch (e) {
            Log.debug(e);
            return 0;
        }
    };

    /**
     * Get duration of video
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getDuration = async function() {
        return await this.player.getDuration();
    };

    /**
     * Set current time of player
     *
     * @param {float} secs time
     * @returns {Promise}
     */
    VideoTime.prototype.setCurrentPosition = async function(secs) {
        return await this.player.setCurrentTime(secs);
    };

    /**
     * Get current time of player
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getCurrentPosition = async function() {
        let position = await this.player.getCurrentTime();
        this.plugins.forEach(async plugin => {
            if (plugin.getCurrentPosition) {
                position = await plugin.getCurrentPosition(position);
            }
        });
        return position;
    };

    return VideoTime;
});
