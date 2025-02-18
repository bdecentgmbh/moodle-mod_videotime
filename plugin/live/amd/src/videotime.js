/*
 * Video time player specific js
 *
 * @package    videotimeplugin_live
 * @module     videotimeplugin_live/videotime
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";
import Config from "core/config";
import VideoTimeBase from "mod_videotime/videotime";
import Janus from 'block_deft/janus-gateway';
import Log from "core/log";
import Notification from "core/notification";
import PublishBase from "block_deft/publish";
import SubscribeBase from "block_deft/subscribe";
import Socket from "videotimeplugin_live/socket";

var rooms = {},
    wstoken;

class Publish extends PublishBase {
    /**
     * Register the room
     *
     * @param {object} pluginHandle
     * @return {Promise}
     */
    async register(pluginHandle) {
        // Try a registration
        try {
            const response = await Ajax.call([{
                args: {
                    handle: pluginHandle.getId(),
                    id: Number(this.contextid),
                    plugin: pluginHandle.plugin,
                    room: this.roomid,
                    ptype: this.ptype == 'publish',
                    session: pluginHandle.session.getSessionId()
                },
                contextid: this.contextid,
                fail: Notification.exception,
                methodname: 'videotimeplugin_live_join_room'
            }])[0];
            this.feed = response.id;

            return response;
        } catch (e) {
            Notification.exception(e);
        }

        return null;
    }

    /**
     * Publish current video feed
     *
     * @returns {Promise}
     */
    publishFeed() {
        return Ajax.call([{
            args: {
                id: Number(this.feed),
                room: this.roomid,
            },
            contextid: this.contextid,
            fail: Notification.exception,
            methodname: 'videotimeplugin_live_publish_feed'
        }])[0];
    }


    /**
     * Stop video feed
     *
     * @returns {Promise}
     */
    unpublish() {
        document.querySelectorAll('#video-controls-camera, #video-controls-display').forEach(video => {
            video.srcObject = null;
            video.parentNode.classList.add('hidden');
        });
        return Ajax.call([{
            args: {
                id: Number(this.feed),
                publish: false,
                room: this.roomid
            },
            contextid: this.contextid,
            fail: Notification.exception,
            methodname: 'videotimeplugin_live_publish_feed'
        }])[0];
    }

    handleClose() {
        document.querySelectorAll(
            '[data-contextid="' + this.contextid + '"][data-action="publish"]'
        ).forEach(button => {
            button.classList.remove('hidden');
        });

        this.janus.destroy();

        [
            this.currentCamera || null,
            this.currentDisplay || null,
        ].forEach(async(videoInput) => {
            if (!videoInput) {
                return;
            }
            const videoStream = await videoInput;
            if (videoStream) {
                videoStream.getTracks().forEach(track => {
                    track.enabled = false;
                    track.stop();
                });
            }
        });
    }

    async onLocalTrack(track, on) {
        if (track.kind == 'audio') {
            return;
        }
        if (on) {
            const remoteStream = new MediaStream([track]);
            remoteStream.mid = track.mid;

            Janus.attachMediaStream(
                document.getElementById('video-controls-' + this.tracks[track.id]),
                remoteStream
            );

            return;
        }
        if (this.currentCamera) {
            const videoStream = await this.currentCamera;
            if (videoStream) {
                videoStream.getTracks().forEach(current => {
                    if (current.id == track.id) {
                        this.currentCamera = null;
                        document
                            .getElementById('video-controls-' + this.tracks[track.id])
                            .parentNode
                            .classList
                            .add('hidden');
                    }
                });
            }
        }
        if (this.currentDisplay) {
            const videoStream = await this.currentDisplay;
            if (videoStream) {
                videoStream.getTracks().forEach(current => {
                    if (current.id == track.id) {
                        this.currentDisplay = null;
                        document
                            .getElementById('video-controls-' + this.tracks[track.id])
                            .parentNode
                            .classList
                            .add('hidden');
                    }
                });
            }
        }
    }

    handleClick(e) {
        const button = e.target.closest(
            '[data-contextid="' + this.contextid + '"][data-action="publish"], [data-contextid="'
            + this.contextid + '"][data-action="close"], [data-contextid="'
            + this.contextid + '"][data-action="mute"], [data-contextid="'
            + this.contextid + '"][data-action="unmute"], [data-contextid="'
            + this.contextid + '"][data-action="switch"], [data-contextid="'
            + this.contextid + '"][data-action="unpublish"]'
        );
        if (button) {
            const action = button.getAttribute('data-action'),
                type = button.getAttribute('data-type') || 'camera';
            e.stopPropagation();
            e.preventDefault();
            document.querySelectorAll(
                '[data-region="deft-venue"] [data-action="publish"], [data-region="deft-venue"] [data-action="unpublish"]'
            ).forEach(button => {
                if ((button.getAttribute('data-action') != action) || (button.getAttribute('data-type') != type)) {
                    button.classList.remove('hidden');
                }
            });
            switch (action) {
                case 'close':
                    document.getElementById('video-controls-' + type).srcObject = null;
                    document.getElementById('video-controls-' + type).parentNode.classList.add('hidden');
                    if (this.tracks[this.selectedTrack.id] == type) {
                        this.unpublish();
                    }
                    break;
                case 'mute':
                case 'unmute':
                    this.muteAudio(action == 'mute', type);
                    break;
                case 'publish':
                    if (type == 'display') {
                        this.videoInput = this.shareDisplay();
                    } else {
                        this.videoInput = this.shareCamera();
                    }
                    document.querySelectorAll('#video-controls-camera, #video-controls-display').forEach(video => {
                        video.parentNode.classList.remove('selected');
                    });
                    document
                        .getElementById('video-controls-' + type)
                        .parentNode
                        .classList
                        .remove('hidden');
                    document
                        .getElementById('video-controls-' + type)
                        .parentNode
                        .classList
                        .add('selected');

                    this.processStream([]);
                    break;
                case 'switch':
                    document.querySelectorAll('#video-controls-camera, #video-controls-display').forEach(video => {
                        video.parentNode.classList.remove('selected');
                    });
                    if (type == 'display') {
                        this.videoInput = this.currentDisplay;
                    } else {
                        this.videoInput = this.currentCamera;
                    }
                    document
                        .getElementById('video-controls-' + type)
                        .parentNode
                        .classList
                        .remove('hidden');
                    document
                        .getElementById('video-controls-' + type)
                        .parentNode
                        .classList
                        .add('selected');
                    this.processStream([]);
                    break;
                case 'unpublish':
                    this.unpublish();
            }
        }

        return true;
    }

    async muteAudio(mute, type) {
        try {
            const videoStream = await ((type == 'display') ? this.currentDisplay : this.currentCamera);
            if (videoStream) {
                videoStream.getAudioTracks().forEach(track => {
                    track.enabled = (!mute);
                });
            }
            return videoStream;
        } catch (e) {
            Notification.exception(e);
        }

        return null;
    }

    /**
     * Set video source to user camera
     */
    async shareCamera() {
        const videoInput = this.videoInput;
        const currentCamera = this.currentCamera;

        if (currentCamera) {
            const videoStream = await currentCamera;
            if (videoStream) {
                return videoStream;
            }
        }

        try {
            this.currentCamera = navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            });
            const videoStream = await this.currentCamera;
            this.tracks = this.tracks || {};
            videoStream.getTracks().forEach(track => {
                this.tracks[track.id] = 'camera';
            });
            return videoStream;
        } catch (e) {
            Log.debug(e);
        }

        return videoInput;
    }

    /**
     * Set video source to display surface
     */
    async shareDisplay() {
        const videoInput = this.videoInput,
            currentDisplay = this.currentDisplay || null,
            displayInput = navigator.mediaDevices.getDisplayMedia({
            video: true,
            audio: true
        });

        this.videoInput = displayInput;
        try {
            const videoStream = await displayInput;
            this.tracks = this.tracks || {};
            videoStream.getTracks().forEach(track => {
                this.tracks[track.id] = 'display';
            });

            this.currentDisplay = displayInput;
            if (currentDisplay) {
                const videoStream = await currentDisplay;
                if (videoStream) {
                    videoStream.getTracks().forEach(track => {
                        track.stop();
                    });
                }
            }

            return videoStream;
        } catch (e) {
            Log.debug(e);

            return videoInput;
        }
    }

    /**
     * Process tracks from current video stream and adjust publicatioin
     *
     * @param {array} tracks Additional tracks to add
     * @returns {bool}
     */
    async processStream(tracks) {
        try {
            const videoStream = await this.videoInput;
            this.tracks = this.tracks || {};
            if (videoStream) {
                const audiotransceiver = this.getTransceiver('audio'),
                    videotransceiver = this.getTransceiver('video');
                videoStream.getVideoTracks().forEach(track => {
                    track.addEventListener('ended', () => {
                        if (this.selectedTrack.id == track.id) {
                            this.unpublish();
                        } else {
                            document
                                .getElementById('video-controls-' + this.tracks[track.id])
                                .parentNode
                                .classList
                                .add('hidden');
                        }
                    });
                    this.selectedTrack = track;
                    if (videotransceiver) {
                        this.videoroom.replaceTracks({
                            tracks: [{
                                type: 'video',
                                mid: videotransceiver.mid,
                                capture: track
                            }],
                            error: Notification.exception
                        });

                        return;
                    }
                    tracks.push({
                        type: 'video',
                        capture: track,
                        recv: false
                    });
                });
                videoStream.getAudioTracks().forEach(track => {
                    if (
                        document.querySelector('.hidden[data-action="mute"][data-contextid="' + this.contextid + '"][data-type="'
                        + this.tracks[this.selectedTrack.id] + '"]'
                    )) {
                        track.enabled = false;
                    }

                    if (audiotransceiver) {
                        this.videoroom.replaceTracks({
                            tracks: [{
                                type: 'audio',
                                mid: audiotransceiver.mid,
                                capture: track
                            }],
                            error: Notification.exception
                        });

                        return;
                    }
                    tracks.push({
                        type: 'audio',
                        capture: track,
                        recv: false
                    });
                });
                if (!tracks.length) {
                    return videoStream;
                }
                this.videoroom.createOffer({
                    tracks: tracks,
                    success: (jsep) => {
                        const publish = {
                            request: "configure",
                            video: true,
                            audio: true
                        };
                        this.videoroom.send({
                            message: publish,
                            jsep: jsep
                        });
                    },
                    error: function(error) {
                        Notification.alert("WebRTC error... ", error.message);
                    }
                });
            }
        } catch (e) {
            Notification.exception(e);
        }

        return true;
    }
}

export default class VideoTime extends VideoTimeBase {
    /**
     * Initialize player plugin
     *
     * @param {int} contextid
     * @param {string} token Deft token
     * @param {int} peerid Peer id for audio room participant
     *
     * @returns {bool}
     */
    async initialize(contextid, token, peerid) {
        Log.debug("Initializing Video Time " + this.elementId);

        this.contextid = contextid;
        this.plugins = [];
        this.peerid = peerid;

        if (this.instance.token) {
            wstoken = this.instance.token;
        }

        try {
            const response = await this.getRoom();
            const socket = new Socket(contextid, token);

            this.iceservers = JSON.parse(response.iceservers);
            this.roomid = response.roomid;
            this.server = response.server;

            rooms[String(contextid)] = {
                contextid: contextid,
                peerid: peerid,
                roomid: response.roomid,
                server: response.server,
                iceServers: JSON.parse(response.iceservers)
            };
            this.roomid = response.roomid;

            document.querySelectorAll('[data-contextid="' + this.contextid + '"] .videotime-control').forEach(control => {
                control.classList.remove('hidden');
            });

            socket.subscribe(async() => {
                try {
                    const response = await this.getFeed();
                    const room = rooms[String(contextid)];
                    if (room.publish && room.publish.restart) {
                        if (response.feed == peerid) {
                            this.unpublish();
                        }
                        room.publish = null;
                    }
                    this.subscribeTo(Number(response.feed));
                } catch (e) {
                    Notification.exception(e);
                }
            });
        } catch (e) {
            Notification.exception(e);
        }

        this.addListeners();

        return true;
    }

    /**
     * Fetch room info
     *
     * @returns {Promise}
     */
    async getRoom() {
        if (wstoken) {
            const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                data = url.searchParams;
            data.set('wstoken', wstoken);
            data.set('moodlewsrestformat', 'json');
            data.set('wsfunction', 'videotimeplugin_live_get_room');
            data.set('contextid', this.contextid);

            try {
                const response = await fetch(url);
                if (!response.ok) {
                    Notification.exeption('Web service error');
                }
                return await response.json();
            } catch (e) {
                Notification.exception(e);
            }
        }

        return await Ajax.call([{
            methodname: 'videotimeplugin_live_get_room',
            args: {contextid: this.contextid},
            fail: Notification.exception
        }])[0];
    }

    /**
     * Fetch current feed
     *
     * @returns {Promise}
     */
    async getFeed() {
        if (wstoken) {
            const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                data = url.searchParams;
            data.set('wstoken', wstoken);
            data.set('moodlewsrestformat', 'json');
            data.set('wsfunction', 'videotimeplugin_live_get_feed');
            data.set('contextid', this.contextid);

            try {
                const response = fetch(url);
                if (!response.ok) {
                    Notification.exeption('Web service error');
                }
                return await response.json();
            } catch (e) {
                Notification.exception(e);
            }
        }

        return await Ajax.call([{
            methodname: 'videotimeplugin_live_get_feed',
            args: {contextid: this.contextid},
            fail: Notification.exception
        }])[0];
    }

    /**
     * Get video element
     *
     * @returns {HTMLMediaElement}
     */
    getPlayer() {
        const player = document.getElementById(this.elementId);

        return player;
    }

    /**
     * Register player events to respond to user interaction and play progress.
     */
    addListeners() {
        const player = this.getPlayer();

        document.querySelector('body').removeEventListener('click', handleClick);
        document.querySelector('body').addEventListener('click', handleClick);

        if (!player) {
            Log.debug('Player was not properly initialized for course module ' + this.cmId);
            return;
        }

        // Fire view event in Moodle on first play only.
        player.addEventListener('play', () => {
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

        // Note: Vimeo player does not support multiple events in a single on() call. Each requires it's own function.

        // Catch all events where video plays.
        player.addEventListener('play', function() {
            this.playing = true;
            Log.debug('VIDEO_TIME play');
        }.bind(this));
        player.addEventListener('playing', function() {
            this.playing = true;
            Log.debug('VIDEO_TIME playing');
        }.bind(this));

        // Catch all events where video stops.
        player.addEventListener('pause', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME pause');
        }.bind(this));
        player.addEventListener('stalled', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME stalled');
        }.bind(this));
        player.addEventListener('suspend', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME suspend');
        }.bind(this));
        player.addEventListener('abort', function() {
            this.playing = false;
            Log.debug('VIDEO_TIME abort');
        }.bind(this));

        // Initiate video finish procedure.
        player.addEventListener('ended', this.handleEnd.bind(this));
        player.addEventListener('pause', this.handlePause.bind(this));
    }

    /**
     * Subscribe to feed
     *
     * @param {int} source Feed to subscribe
     */
    subscribeTo(source) {
        const room = rooms[String(this.contextid)];
        document.querySelectorAll('[data-contextid="' + this.contextid + '"][data-action="publish"]').forEach(button => {
            if (source == Number(this.peerid)) {
                button.classList.remove('hidden');
            } else {
                button.classList.remove('hidden');
            }
        });
        document.querySelectorAll('[data-contextid="' + this.contextid + '"][data-action="unpublish"]').forEach(button => {
            if (source == Number(this.peerid)) {
                button.classList.remove('hidden');
            } else {
                button.classList.remove('hidden');
            }
        });

        if (this.remoteFeed && !this.remoteFeed.creatingSubscription && !this.remoteFeed.restart) {
            this.updateSubscription(source);
        } else if (this.remoteFeed && this.remoteFeed.restart) {
            if (this.remoteFeed.current != source) {
                this.remoteFeed = null;
                this.subscribeTo(source);
            }
        } else if (this.remoteFeed) {
            setTimeout(() => {
                this.subscribeTo(source);
            }, 500);
        } else if (source) {
            this.remoteFeed = new Subscribe(this.contextid, this.iceservers, this.roomid, this.server, this.peerid);
            this.remoteFeed.remoteVideo = document.getElementById(this.elementId);
            this.remoteFeed.remoteAudio = document.getElementById(this.elementId).parentNode.querySelector('audio');
            this.remoteFeed.muteAudio = room.publish && (room.publish.feed === source);
            this.remoteFeed.startConnection(source);
            document.querySelectorAll('[data-contextid="' + this.contextid + '"] img.poster-img').forEach(img => {
                img.classList.add('hidden');
            });
            document.querySelectorAll('[data-contextid="' + this.contextid + '"] video').forEach(img => {
                img.classList.remove('hidden');
            });
        }
    }

    updateSubscription(source) {
        const room = rooms[String(this.contextid)];
        const update = {
            request: 'update',
            subscribe: [{
                feed: Number(source)
            }],
            unsubscribe: [{
                feed: Number(this.remoteFeed.current)
            }]
        };

        if (!source && this.remoteFeed.current) {
            delete update.subscribe;
        } else if (source && !this.remoteFeed.current) {
            delete update.unsubscribe;
        }

        if (this.remoteFeed.current != source) {
            this.remoteFeed.muteAudio = room.publish && (room.publish.feed === source);
            this.remoteFeed.videoroom.send({message: update});
            if (this.remoteFeed.audioTrack) {
                this.remoteFeed.audioTrack.enabled = !this.remoteFeed.muteAudio;
            }

            if (room.publish && this.remoteFeed.current == room.publish.feed) {
                room.publish.handleClose();
                room.publish = null;
            }
            this.remoteFeed.current = source;
            if (!source && this.remoteFeed) {
                this.remoteFeed.handleClose();
                this.remoteFeed = null;
            }
            if (Number(source)) {
                document.querySelectorAll(
                    '[data-contextid="' + this.contextid + '"] .videotime-embed img.poster-img'
                ).forEach(img => {
                    img.classList.add('hidden');
                });
                document.querySelectorAll(
                    '[data-contextid="' + this.contextid + '"] .videotime-embed video'
                ).forEach(video => {
                    video.classList.remove('hidden');
                });
            } else {
                document.querySelectorAll(
                    '[data-contextid="' + this.contextid + '"] .videotime-embed img.poster-img'
                ).forEach(img => {
                    img.classList.remove('hidden');
                });
                document.querySelectorAll(
                    '[data-contextid="' + this.contextid + '"] .videotime-embed video'
                ).forEach(video => {
                    video.srcObject = null;
                    video.classList.add('hidden');
                });
            }
        }
    }

    /**
     * Get duration of video
     *
     * @returns {float}
     */
    getDuration() {
        return 0;
    }

    /**
     * Get duration of video
     *
     * @returns {float}
     */
    getPlaybackRate() {
        return 1;
    }

    /**
     * Get pause state
     *
     * @return {bool}
     */
    getPaused() {
        return true;
    }

    /**
     * Record watch time for session.
     */
    recordWatchTime() {
        return;
    }

    /**
     * Set state on session.
     */
    setSessionState() {
        return;
    }

    /**
     * Set current watch time for video. Used for resuming.
     */
    setCurrentTime() {
        return;
    }

    /**
     * Set video watch percentage for session.
     */
    setPercent() {
        return;
    }
}

const handleClick = function(e) {
    const button = e.target.closest(
        '[data-roomid] [data-action="publish"], [data-roomid] [data-action="unpublish"],'
        + '[data-roomid] [data-action="close"], '
        + '[data-roomid] [data-action="switch"], '
        + '[data-roomid] [data-action="mute"], [data-roomid] [data-action="unmute"]'
    );
    if (button) {
        const action = button.getAttribute('data-action'),
            contextid = e.target.closest('[data-contextid]').getAttribute('data-contextid'),
            room = rooms[String(contextid)],
            iceServers = room.iceServers,
            peerid = room.peerid,
            roomid = room.roomid,
            server = room.server,
            type = button.getAttribute('data-type');
        e.stopPropagation();
        e.preventDefault();
        if ((action == 'publish') && (!room.publish || room.publish.restart)) {
            room.publish = new Publish(contextid, iceServers, roomid, server, peerid);
            if (type == 'display') {
                room.publish.videoInput = room.publish.shareDisplay();
            } else {
                room.publish.videoInput = room.publish.shareCamera();
            }
            room.publish.startConnection();
            document
                .getElementById('video-controls-' + (type || 'camera'))
                .parentNode
                .classList
                .remove('hidden');
            document
                .getElementById('video-controls-' + (type || 'camera'))
                .parentNode
                .classList
                .add('selected');
        } else {
            if ((action == 'mute') || (action == 'unmute')) {
                button.classList.add('hidden');
                button.parentNode.querySelectorAll('[data-action="mute"], [data-action="unmute"]').forEach(button => {
                    if (button.getAttribute('data-action') != action) {
                        button.classList.remove('hidden');
                    }
                });
            }
            if (room.publish) {
                room.publish.handleClick(e);
            }
        }
    }
};

class Subscribe extends SubscribeBase {
    /**
     * Register the room
     *
     * @param {object} pluginHandle
     * @return {Promise}
     */
    async register(pluginHandle) {
        // Try a registration
        if (wstoken) {
            const url = new URL(Config.wwwroot + '/webservice/rest/server.php'),
                data = url.searchParams;
            data.set('wstoken', wstoken);
            data.set('moodlewsrestformat', 'json');
            data.set('wsfunction', 'videotimeplugin_live_join_room');
            data.set('handle', pluginHandle.getId());
            data.set('id', Number(this.contextid));
            data.set('plugin', pluginHandle.plugin);
            data.set('room', this.roomid);
            data.set('feed', this.feed);
            data.set('session', pluginHandle.session.getSessionId());
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    Notification.exeption('Web service error');
                }
                return await response.json();
            } catch (e) {
                Notification.exception(e);
            }
        }

        return await Ajax.call([{
            args: {
                handle: pluginHandle.getId(),
                id: Number(this.contextid),
                plugin: pluginHandle.plugin,
                room: this.roomid,
                ptype: false,
                feed: this.feed,
                session: pluginHandle.session.getSessionId()
            },
            contextid: this.contextid,
            fail: Notification.exception,
            methodname: 'videotimeplugin_live_join_room'
        }])[0];
    }

    /**
     * Attach audio stream to media element
     *
     * @param {HTMLMediaElement} audioStream Stream to attach
     */
    attachAudio(audioStream) {
        Janus.attachMediaStream(
            this.remoteVideo.parentNode.querySelector('audio'),
            audioStream
        );
        audioStream.getTracks().forEach(track => {
            this.audioTrack = track;
            track.enabled = !this.muteAudio;
        });
    }

    /**
     * Attach video stream to media element
     *
     * @param {HTMLMediaElement} videoStream Stream to attach
     */
    attachVideo(videoStream) {
        Janus.attachMediaStream(
            this.remoteVideo,
            videoStream
        );
    }
}
