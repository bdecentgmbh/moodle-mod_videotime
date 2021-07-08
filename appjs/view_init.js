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

            for (let i = 0; i < this.players.length; i++) {
                this.players[i].destroy();
            }

            window.setTimeout(function() {
                let embeds = document.getElementsByClassName('vimeo-embed');

                for (let i = 0; i < embeds.length; i++) {
                    if (embeds.hasOwnProperty(i)) {
                        var v = new VimeoVideo(embeds[i], page);
                        v.init();
                        self.players.push(v);
                    }
                }
            }, 0);
        }
    };

    function VimeoVideo(element, angularComponent) {
        this.element = element;
        this.angularComponent = angularComponent;
        this.cmid = element.getAttribute('data-session');
        this.hasPro = element.getAttribute('data-has-pro') && element.getAttribute('data-has-pro') !== '0';
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
            this.session = await this.getNewSession();
            this.resumeTime = await this.getResumeTime();
            this.instance = await this.getInstance();
        }
        this.setupPlayer();
    };

    VimeoVideo.prototype.destroy = function() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    };

    VimeoVideo.prototype.getNewSession = function () {
        return this.angularComponent.CoreSitesProvider.getCurrentSite().write('videotimeplugin_pro_get_new_session',
            {cmid: this.element.getAttribute('data-cmid')});
    };

    VimeoVideo.prototype.getResumeTime = function() {
        return this.angularComponent.CoreSitesProvider.getCurrentSite().write('videotimeplugin_pro_get_resume_time',
            {cmid: this.element.getAttribute('data-cmid')});
    };

    VimeoVideo.prototype.getInstance = function() {
        return this.angularComponent.CoreSitesProvider.getCurrentSite().write('mod_videotime_get_videotime',
            {cmid: this.element.getAttribute('data-cmid')});
    };

    VimeoVideo.prototype.setupPlayer = function () {
        const self = this;

        if (!this.hasPro) {
            this.player = new Player(this.element.id, {});
            return;
        } else {
            this.player = new Player(this.element.id, this.instance);
        }

        if (this.resumeTime) {
            this.player.on('loaded', function() {
                self.player.setCurrentTime(self.resumeTime.seconds);
            });
        }

        this.player.on('play', function () {
            self.playing = true;
            console.log('VIDEO_TIME play');
        });
        this.player.on('playing', function () {
            self.playing = true;
            console.log('VIDEO_TIME playing');
        });
        this.player.on('pause', function () {
            self.playing = false;
            console.log('VIDEO_TIME pause');
        });
        this.player.on('stalled', function () {
            self.playing = false;
            console.log('VIDEO_TIME stalled');
        });
        this.player.on('suspend', function () {
            self.playing = false;
            console.log('VIDEO_TIME suspend');
        });
        this.player.on('abort', function () {
            self.playing = false;
            console.log('VIDEO_TIME abort');
        });

        this.player.on('timeupdate', function (event) {
            self.percent = event.percent;
            self.currentTime = event.seconds;
            console.log('VIDEO_TIME timeupdate. Percent: ' + self.percent + '. Current time: ' + self.currentTime);
        });

        this.interval = setInterval(function () {
            if (self.playing) {
                self.time++;
                if (self.time % 5 === 0) {
                    self.angularComponent.CoreSitesProvider.getCurrentSite().write('videotimeplugin_pro_record_watch_time',
                        {session_id: self.session.id, time: self.time}).then(function() {
                        self.angularComponent.CoreSitesProvider.getCurrentSite().write('videotimeplugin_pro_set_percent',
                            {session_id: self.session.id, percent: self.percent}).then(function() {
                            self.angularComponent.CoreSitesProvider.getCurrentSite().write('videotimeplugin_pro_set_session_current_time',
                                {session_id: self.session.id, current_time: self.currentTime});
                        });
                    });
                }
            }
        }, 1000);
    };
})(this);