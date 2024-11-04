/* eslint 'promise/no-native': "off" */
/* eslint 'no-console': "off" */
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

    VimeoVideo.prototype.init = async function() {
        if (this.hasPro) {
            try {
                this.session = await this.getNewSession();
                this.resumeTime = await this.getResumeTime();
                this.instance = await this.getInstance();
            } catch (e) {
                console.log(e);
            }
        }
        this.setupPlayer();
    };

    VimeoVideo.prototype.destroy = function() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    };

    VimeoVideo.prototype.getNewSession = async function() {
        return await this.angularComponent.CoreSitesProvider.getCurrentSite().write(
            'videotimeplugin_pro_get_new_session',
            {
                cmid: this.element.getAttribute('data-cmid')
            }
        );
    };

    VimeoVideo.prototype.getResumeTime = async function() {
        return await this.angularComponent.CoreSitesProvider.getCurrentSite().write(
            'videotimeplugin_pro_get_resume_time',
            {
                cmid: this.element.getAttribute('data-cmid')
            }
        );
    };

    VimeoVideo.prototype.getInstance = async function() {
        return await this.angularComponent.CoreSitesProvider.getCurrentSite().write(
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

        this.interval = setInterval(async function() {
            if (self.playing) {
                self.time++;
                if (self.time % 5 === 0) {
                    try {
                        await self.angularComponent.CoreSitesProvider.getCurrentSite()
                            .write('videotimeplugin_pro_record_watch_time', {
                                "session_id": self.session.id,
                                time: self.time
                            });
                        await self.angularComponent.CoreSitesProvider.getCurrentSite()
                            .write('videotimeplugin_pro_set_percent', {
                                "session_id": self.session.id,
                                percent: self.percent
                            });
                        await self.angularComponent.CoreSitesProvider.getCurrentSite().write(
                                'videotimeplugin_pro_set_session_current_time',
                                {
                                    "session_id": self.session.id,
                                    "current_time": self.currentTime
                                }
                            );
                    } catch (e) {
                        console.log(e);
                    }
                }
            }
        }, 1000);
    };
})(this);
