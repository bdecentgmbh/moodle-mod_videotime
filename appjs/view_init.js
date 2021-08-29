var Player;
(function(t) {
    t.CoreUtilsProvider.videoTimeUtils = {
        players: [],

        /**
         * Trigger loading or extra messages to fill the page if required, and set the content height.
         *
         * @param {CoreCompileFakeHTMLComponent} page Must define a loadMoreMessages function.
         */
        pageInit: function(page) {
            var self = this;

            for (var i = 0; i < this.players.length; i++) {
                this.players[i].destroy();
            }

            window.setTimeout(function() {
                var embeds = document.getElementsByClassName('vimeo-embed');

                for (var i = 0; i < embeds.length; i++) {
                    if (embeds.hasOwnProperty(i)) {
                        var v = new VimeoVideo(embeds[i], page);
                        v.init();
                        self.players.push(v);
                    }
                }
            }, 0);
        }
    };

    /**
     *
     * VimeoVideo constructor
     *
     * @param {int} element
     * @param {angularComponent} angularComponent
     */
    function VimeoVideo(element, angularComponent) {
        this.element = element;
        this.angularComponent = angularComponent;
        this.cmid = element.getAttribute('data-session');
        this.hasPro = element.getAttribute('data-has-pro');
        this.resumeTime = element.getAttribute('data-resume-time');
        this.player = null;
        this.playing = false;
        this.time = 0;
        this.percent = 0;
        this.currentTime = 0;
        this.interval = null;
        this.instance = {};

        this.session = null;
    }

    VimeoVideo.prototype.init = function() {
        if (this.hasPro) {
            this.getNewSession()
                .then(
                    function(session) {
                        this.session = session;
                    }.bind(this)
                )
                .catch();
            this.getResumeTime()
                .then(
                    function(resumetime) {
                        this.resumeTime = resumetime;
                    }.bind(this)
                )
                .catch();
            this.getInstance()
                .then(
                    function(instance) {
                        this.instance = instance;
                    }.bind(this)
                )
                .catch();
        }
        this.setupPlayer();
    };

    VimeoVideo.prototype.destroy = function() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    };

    VimeoVideo.prototype.getNewSession = function() {
        return this.angularComponent.CoreSitesProvider.getCurrentSite().write(
            'videotimeplugin_pro_get_new_session',
            {
                cmid: this.element.getAttribute('data-cmid')
            }
        );
    };

    VimeoVideo.prototype.getResumeTime = function() {
        return this.angularComponent.CoreSitesProvider.getCurrentSite().write(
            'videotimeplugin_pro_get_resume_time',
            {
                cmid: this.element.getAttribute('data-cmid')
            }
        );
    };

    VimeoVideo.prototype.getInstance = function() {
        return this.angularComponent.CoreSitesProvider.getCurrentSite().write(
            'mod_videotime_get_videotime',
            {
                cmid: this.element.getAttribute('data-cmid')
            }
        );
    };

    VimeoVideo.prototype.setupPlayer = function() {
        var self = this;

        if (!this.hasPro) {
            this.player = new Player(this.element.id, {});
            return;
        } else {
            this.player = new Player(this.element.id, {
                autoplay: this.instance.autoplay,
                byline: this.instance.byline,
                color: this.instance.color,
                maxheight: this.instance.maxheight,
                maxwidth: this.instance.maxwidth,
                muted: this.instance.muted,
                playsinline: this.instance.playsinline,
                responsive: this.instance.responsive,
                speed: this.instance.speed,
                title: this.instance.title,
                transparent: this.instance.transparent,
                url: this.instance.url,
                width: this.instance.width
            });
        }

        if (this.resumeTime) {
            this.player.on('loaded', function() {
                self.player.setCurrentTime(self.resumeTime.seconds);
            });
        }

        this.player.on('play', function() {
            self.playing = true;
        });
        this.player.on('playing', function() {
            self.playing = true;
        });
        this.player.on('pause', function() {
            self.playing = false;
        });
        this.player.on('stalled', function() {
            self.playing = false;
        });
        this.player.on('suspend', function() {
            self.playing = false;
        });
        this.player.on('abort', function() {
            self.playing = false;
        });

        this.player.on('timeupdate', function(event) {
            self.percent = event.percent;
            self.currentTime = event.seconds;
        });

        this.interval = setInterval(function() {
            if (self.playing) {
                self.time++;
                if (self.time % 5 === 0) {
                    self.angularComponent.CoreSitesProvider.getCurrentSite()
                        .write('videotimeplugin_pro_record_watch_time', {
                            "session_id": self.session.id,
                            time: self.time
                        })
                        .then(function() {
                            self.angularComponent.CoreSitesProvider.getCurrentSite()
                                .write('videotimeplugin_pro_set_percent', {
                                    "session_id": self.session.id,
                                    percent: self.percent
                                })
                                .then(function() {
                                    self.angularComponent.CoreSitesProvider.getCurrentSite().write(
                                        'videotimeplugin_pro_set_session_current_time',
                                        {
                                            "session_id": self.session.id,
                                            "current_time": self.currentTime
                                        }
                                    );
                                    return true;
                                })
                                .catch();
                            return true;
                        })
                        .catch();
                }
            }
        }, 1000);
    };
})(this);
