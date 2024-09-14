define([
    "jquery",
    "mod_videotime/videotime",
    "core/log",
    "core/notification",
    "media_videojs/video-lazy",
    "media_videojs/Youtube-lazy"
], function ($, VideoTimeBase, Log, Notification, Player) {
    function VideoTime(elementId, cmId, hasPro, interval, instance) {
        VideoTimeBase.call(this, elementId, cmId, hasPro, interval, instance);
    }

    VideoTime.prototype = Object.create(VideoTimeBase.prototype);
    VideoTime.prototype.constructor = VideoTime;

    VideoTime.prototype.initialize = function () {
        Log.debug("Initializing Video Time " + this.elementId);
        let instance = this.instance,
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

        let url = new URL(window.location.href),
            q = url.searchParams.get("q"),
            starttime = (url.searchParams.get("time") || "").match(
                /^([0-9]+:){0,2}([0-9]+)(\.[0-9]+)$/
            );
        if (starttime) {
            this.setStartTime(starttime[0])
                .then(function() {
                    if (q && window.find) {
                        window.find(q);
                    }
                    return true;
                })
                .catch(Notification.exception);
        } else if (q && window.find) {
            window.find(q);
        }

        this.addListeners();

        for (let i = 0; i < this.plugins.length; i++) {
            const plugin = this.plugins[i];
            plugin.initialize(this, instance);
        }

        return true;
    };

    /**
     * Register player events to respond to user interaction and play progress.
     */
    VideoTime.prototype.addListeners = async function () {
        // If this is a tab play set time cues and listener.
        $($("#" + this.elementId).closest(".videotimetabs")).each(
            function(i, tabs) {
                $(tabs)
                    .find('[data-action="cue"]')
                    .each(
                        function(index, anchor) {
                            let starttime = anchor.getAttribute("data-start"),
                                time = starttime.match(
                                    /((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/
                                );
                            if (time) {
                                this.player
                                    .addCuePoint(
                                        3600 * Number(time[3] || 0) +
                                            60 * Number(time[5] || 0) +
                                            Number(time[6]),
                                        {
                                            starttime: starttime
                                        }
                                    )
                                    .catch(Notification.exeception);
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
    };

    VideoTime.prototype.setStartTime = function (starttime) {
        let time = starttime.match(
            /((([0-9]+):)?(([0-9]+):))?([0-9]+(\.[0-9]+))/
        );
        if (time) {
            this.resumeTime =
                3600 * Number(time[3] || 0) +
                60 * Number(time[5] || 0) +
                Number(time[6]);
            return this.player.currentTime(this.resumeTime);
        }
        Log.debug("Set start time:" + starttime);
        return this.player.currentTime();
    };

    VideoTime.prototype.getDuration = function () {
        return new Promise(resolve => {
            resolve(this.player.duration());
            return true;
        });
    };

    VideoTime.prototype.getPlaybackRate = function () {
        return new Promise(resolve => {
            resolve(this.player.playbackRate());
            return true;
        });
    };

    VideoTime.prototype.setCurrentPosition = function (secs) {
        return new Promise(resolve => {
            resolve(this.player.currentTime(secs));
            return true;
        });
    };

    /**
     * Get current time of player
     *
     * @returns {Promise}
     */
    VideoTime.prototype.getCurrentPosition = async function () {
        let position = await this.player.currentTime();
        this.plugins.forEach(plugin => {
            position = plugin.getCurrentPosition(position);
        });
        return position;
    };

    return VideoTime;
});
