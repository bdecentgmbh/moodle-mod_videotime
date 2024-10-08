/*
 * Video time player specific js
 *
 * @package    videotimeplugin_videojs
 * @module     videotimeplugin_videojs/videotime
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from "jquery";
import VideoTimeBase from "mod_videotime/videotime";
import Log from "core/log";
import Notification from "core/notification";
import Player from "media_videojs/video-lazy";
import "media_videojs/Youtube-lazy";

export default class VideoTime extends VideoTimeBase {
    initialize() {
        Log.debug("Initializing Video Time " + this.elementId);

        const instance = this.instance,
            options = {
                autoplay: Number(instance.autoplay),
                controls: Number(instance.controls),
                sources: [{type: instance.type, src: instance.vimeo_url}],
                loop: Number(instance.option_loop),
                fluid: Number(instance.responsive),
                playsinline: Number(instance.playsinline),
                playbackRates: Number(instance.speed)
                    ? [0.5, 0.75, 1, 1.25, 1.5, 2]
                    : [1],
                muted: Number(instance.muted)
            };
        if (instance.type === "video/youtube") {
            options.techOrder = ["youtube"];
        }
        if (!Number(instance.responsive) && Number(instance.height) && Number(instance.width)) {
            options.height = Number(instance.height);
            options.width = Number(instance.width);
        }
        Log.debug("Initializing VideoJS player with options:");
        Log.debug(options);
        this.player = new Player(this.elementId, options);

        this.player.on("loadedmetadata", () => {
            if (!instance.resume_playback || instance.resume_time <= 0 || this.resumed) {
                return true;
            }

            let duration = this.getPlayer().duration(),
                resumeTime = instance.resume_time;
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
            if (resumeTime) {
                setTimeout(() => {
                this.setCurrentPosition(resumeTime);
                }, 10);
            }
            return true;
        });

        this.handleStartTime();

        this.addListeners();

        for (let i = 0; i < this.plugins.length; i++) {
            const plugin = this.plugins[i];
            plugin.initialize(this, instance);
        }

        return true;
    }

    /**
     * Register player events to respond to user interaction and play progress.
     */
    async addListeners() {
        // If this is a tab play set time cues and listener.
        $($("#" + this.elementId).closest(".videotimetabs")).each(
            function(i, tabs) {
                $(tabs)
                    .find('[data-action="cue"]')
                    .each(
                        async function(index, anchor) {
                            let starttime = anchor.getAttribute("data-start"),
                                time = starttime.match(
                                    /((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/
                                );
                            if (time) {
                                try {
                                    await this.player.addCuePoint(
                                        3600 * Number(time[3] || 0) +
                                            60 * Number(time[5] || 0) +
                                            Number(time[6]),
                                        {
                                            starttime: starttime
                                        }
                                    );
                                } catch (e) {
                                    Notification.exception(e);
                                }
                            }
                        }.bind(this)
                    );

                this.player.on("cuepoint", function(event) {
                    if (event.data.starttime) {
                        $(tabs)
                            .find(".videotime-highlight")
                            .removeClass("videotime-highlight");
                        $(tabs)
                            .find(
                                '[data-action="cue"][data-start="' +
                                    event.data.starttime +
                                    '"]'
                            )
                            .closest(".row")
                            .addClass("videotime-highlight");
                        $(".videotime-highlight").each(function() {
                            if (this.offsetTop) {
                                this.parentNode.scrollTo({
                                    top: this.offsetTop - 50,
                                    left: 0,
                                    behavior: "smooth"
                                });
                            }
                        });
                    }
                });
            }.bind(this)
        );

        if (!this.player) {
            Log.debug(
                "Player was not properly initialized for course module " +
                    this.cmId
            );
            return;
        }

        // Fire view event in Moodle on first play only.
        this.player.on("play", () => {
            if (!this.played) {
                if (this.hasPro) {
                    this.startWatchInterval();
                }
                // Free version can still mark completion on video time view.
                this.view();
            }
            return true;
        });

        // Features beyond this point are for pro only.
        if (!this.hasPro) {
            return;
        }

        // Note: Vimeo player does not support multiple events in a single on() call. Each requires it's own function.

        // Catch all events where video plays.
        this.player.on(
            "play",
            function() {
                this.playing = true;
                Log.debug("VIDEO_TIME play");
            }.bind(this)
        );
        this.player.on(
            "playing",
            function() {
                this.playing = true;
                Log.debug("VIDEO_TIME playing");
            }.bind(this)
        );

        // Catch all events where video stops.
        this.player.on(
            "pause",
            function() {
                this.playing = false;
                Log.debug("VIDEO_TIME pause");
            }.bind(this)
        );
        this.player.on(
            "stalled",
            function() {
                this.playing = false;
                Log.debug("VIDEO_TIME stalled");
            }.bind(this)
        );
        this.player.on(
            "suspend",
            function() {
                this.playing = false;
                Log.debug("VIDEO_TIME suspend");
            }.bind(this)
        );
        this.player.on(
            "abort",
            function() {
                this.playing = false;
                Log.debug("VIDEO_TIME abort");
            }.bind(this)
        );

        this.player.on(
            "playbackrateschange",
            function() {
                this.playbackRate = this.player.playbackRate();
            }.bind(this)
        );

        // Always update internal values for percent and current time watched.
        this.player.on(
            "timeupdate",
            function() {
                this.currentTime = this.player.currentTime();
                this.percent = this.currentTime / this.player.duration();
                Log.debug(
                    "VIDEO_TIME timeupdate. Percent: " +
                        this.percent +
                        ". Current time: " +
                        this.currentTime
                );
            }.bind(this)
        );

        // Initiate video finish procedure.
        this.player.on("ended", this.handleEnd.bind(this));
        this.player.on("pause", this.handlePause.bind(this));

        // Readjust height when responsive player is resized.
        if (this.player.options().responsive) {
            let observer = new ResizeObserver(() => {
                this.player.height(
                    (this.player.videoHeight() / this.player.videoWidth()) *
                        this.player.currentWidth()
                );
            });
            observer.observe(document.querySelector("#" + this.elementId));
        }
    }

    /**
     * Parse start time and set player
     *
     * @param {string} starttime
     * @returns {Promise}
     */
    async setStartTime(starttime) {
        let time = starttime.match(
            /((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/
        );
        if (time) {
            this.resumeTime =
                3600 * Number(time[3] || 0) +
                60 * Number(time[5] || 0) +
                Number(time[6]);
            return await this.player.currentTime(this.resumeTime);
        }
        Log.debug("Set start time:" + starttime);
        return await this.player.currentTime();
    }

    /**
     * Get play back rate
     *
     * @returns {Promise}
     */
    getDuration() {
        return new Promise(resolve => {
            resolve(this.player.duration());
            return true;
        });
    }

    /**
     * Get duration of video
     *
     * @returns {Promise}
     */
    getPlaybackRate() {
        return new Promise(resolve => {
            resolve(this.player.playbackRate());
            return true;
        });
    }

    /**
     * Set current time of player
     *
     * @param {float} secs time
     * @returns {Promise}
     */
    setCurrentPosition(secs) {
        return new Promise(resolve => {
            resolve(this.player.currentTime(secs));
            return true;
        });
    }

    /**
     * Get current time of player
     *
     * @returns {Promise}
     */
    async getCurrentPosition() {
        let position = await this.player.currentTime();
        this.plugins.forEach(plugin => {
            if (plugin.getCurrentPosition) {
                position = plugin.getCurrentPosition(position);
            }
        });
        return position;
    }

    /**
     * Get pause state
     *
     * @return {bool}
     */
    getPaused() {
        const paused = this.player.paused();
        return paused;
    }
}
