/*
 * @package    mod_videotime
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Load chapters into tab
 *
 * @package    videotimetab_chapter
 * @module     videotimetab_chapter/loadchapters
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from "jquery";
import VideoTimeBase from "mod_videotime/videotime";
import Log from "core/log";
import Notification from "core/notification";
import Player from "media_videojs/video-lazy";
import Templates from "core/templates";

export default class VideoTime extends VideoTimeBase {

    initialize() {
        Log.debug('Initializing Video Time ' + this.elementId);

        this.getInstance().then((instance) => {
            let options = {
                autoplay: instance.autoplay,
                controls: instance.controls,
                responsive: instance.responsive,
                muted: instance.muted
            };
            if (!instance.responsive && instance.height && instance.width) {
                options.height = instance.height;
                options.width = instance.width;
            }
            Log.debug('Initializing VideoJS player with options:');
            this.player = new Player(this.elementId, options);

            let url = new URL(window.location.href),
                q = url.searchParams.get('q'),
                starttime = (url.searchParams.get('time') || '').match(/^([0-9]+:){0,2}([0-9]+)(\.[0-9]+)$/);
            if (starttime) {
                this.setStartTime(starttime[0]).then(function() {
                    if (q && window.find) {
                        window.find(q);
                    }
                    return true;
                }).catch(Notification.exception);
            } else if (q && window.find) {
                window.find(q);
            }

            this.addListeners();

            for (let i = 0; i < this.plugins.length; i++) {
                const plugin = this.plugins[i];
                plugin.initialize(this, instance);
            }

            return true;
        }).catch(Notification.exeption);
    }

    /**
     * Register player events to respond to user interaction and play progress.
     */
    addListeners() {
        if (!this.player) {
            Log.debug('Player was not properly initialized for course module ' + this.cmId);
            return;
        }

        // Fire view event in Moodle on first play only.
        this.player.on('play', () => {
            if (!this.played) {
                if (this.hasPro) {
                    // Getting a new session on first play.
                    this.getSession().then(() => {
                        this.view();
                        this.startWatchInterval();
                        return true;
                    }).catch(Notification.exception);
                } else {
                    // Free version can still mark completion on video time view.
                    this.view();
                }
            }
            return true;
        });

        // Features beyond this point are for pro only.
        if (!this.hasPro) {
            return;
        }

        // If resume is present force seek the player to that point.
        this.getResumeTime().then((seconds) => {
            if (seconds <= 0) {
                return true;
            }

            this.player.on('loaded', () => {
                let duration = this.getPlayer().duration(),
                    resumeTime = seconds;
                // Duration is often a little greater than a resume time at the end of the video.
                // A user may have watched 100 seconds when the video ends, but the duration may be
                // 100.56 seconds. BUT, sometimes the duration is rounded depending on when the
                // video loads, so it may be 101 seconds. Hence the +1 and Math.floor usage.
                if (seconds + 1 >= Math.floor(duration)) {
                    Log.debug('VIDEO_TIME video finished, resuming at start of video.');
                    resumeTime = 0;
                }
                Log.debug('VIDEO_TIME resuming at ' + resumeTime);
                this.player.setCurrentTime(resumeTime);
            });
            return true;
        }).catch(Notification.exception);

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

        this.player.on('playbackratechange', function() {
            this.playbackRate = this.player.playbackRate();
        }.bind(this));

        // Always update internal values for percent and current time watched.
        this.player.on('timeupdate', function() {
            this.currentTime = this.player.currentTime();
            this.percent = 100 * this.currentTime / this.player.duration();
            Log.debug('VIDEO_TIME timeupdate. Percent: ' + this.percent + '. Current time: ' + this.currentTime);
        }.bind(this));

        // Initiate video finish procedure.
        this.player.on('ended', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME ended');

            new Promise(function(resolve) {
                this.getSession().then(function(session) {
                    resolve(session);
                    return true;
                }).catch(Notification.exception);
            }.bind(this)).then(function(session) {
                this.setSessionState(session.id, 1);
                return session;
            }.bind(this)).then(function(session) {
                this.setPercent(session.id, 1);
                return session;
            }.bind(this)).then(function(session) {
                this.setCurrentTime(session.id, this.currentTime);
                return session;
            }.bind(this)).catch(Notification.exception).finally(function() {
                this.getSession().then(function(session) {
                    this.getNextActivityButtonData(session.id).then(function(response) {
                        let data = JSON.parse(response.data);

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

                        Templates.render('videotime/next_activity_button', JSON.parse(response.data))
                            .then(function(html) {
                                $('#next-activity-button').html(html);
                                return true;
                            }).fail(Notification.exception);
                        return true;
                    }).fail(Notification.exception);
                }.bind(this)).catch(Notification.exception);
            }.bind(this)).fail(Notification.exception);
        }.bind(this));

        // If this is a tab play set time cues and listener.
        $($('#' + this.elementId).closest('.videotimetabs')).each(function(i, tabs) {
           $(tabs).find('[data-action="cue"]').each(function(index, anchor) {
                let starttime = anchor.getAttribute('data-start'),
                    time = starttime.match(/((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/);
                if (time) {
                    this.player.addCuePoint(
                        3600 * Number(time[3] || 0) + 60 * Number(time[5] || 0) + Number(time[6]),
                        {
                            starttime: starttime
                        }
                    ).catch(Notification.exeception);
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

        // Readjust height when responsive player is resized.
        if (this.player.options().responsive) {
            let observer = new ResizeObserver(() => {
                this.player.height(this.player.videoHeight() / this.player.videoWidth() * this.player.currentWidth());
            });
            observer.observe(document.querySelector('#' + this.elementId));
        }
    }

    /**
     * Parse start time and set player
     *
     * @param {string} starttime
     * @returns {Promise}
     */
    setStartTime(starttime) {
        let time = starttime.match(/((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/);
        if (time) {
            this.resumeTime = 3600 * Number(time[3] || 0) + 60 * Number(time[5] || 0) + Number(time[6]);
            return this.player.currentTime(this.resumeTime);
        }
        Log.debug('Set start time:' + starttime);
        return this.player.currentTime();
    }
}
