/*
 * Video time player specific js
 *
 * @package    videotimeplugin_live
 * @module     videotimeplugin_live/videotime
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";
import VideoTimeBase from "mod_videotime/videotime";
import Janus from 'block_deft/janus-gateway';
import Log from "core/log";
import Notification from "core/notification";
import PublishBase from "block_deft/publish";
import SubscribeBase from "block_deft/subscribe";
import Socket from "videotimeplugin_live/socket";

var rooms = {};

class Publish extends PublishBase {
    /**
     * Register the room
     *
     * @param {object} pluginHandle
     * @return {Promise}
     */
    register(pluginHandle) {
        // Try a registration
        return Ajax.call([{
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
        }])[0].then(response => {
            this.feed = response.id;
        }).catch(Notification.exception);
    }

    /**
     * Publish current video feed
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
     */
    unpublish() {
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
            this.currentCamera || Promise.resolve(null),
            this.currentDisplay || Promise.resolve(null),
        ].forEach(videoInput => {
            videoInput.then(videoStream => {
                if (videoStream) {
                    videoStream.getVideoTracks().forEach(track => {
                        track.enabled = false;
                        track.stop();
                    });
                }

                return null;
            }).catch(Notification.exception);
        });
    }

    onLocalTrack(track, on) {
        const remoteStream = new MediaStream([track]);
        if (!on || (track.kind == 'audio')) {
            return;
        }
        remoteStream.mid = track.mid;
        Log.debug(on);
        Log.debug(remoteStream);
        Janus.attachMediaStream(
            document.getElementById('video-controls-' + this.tracks[track.id]),
            remoteStream
        );
    }

    handleClick(e) {
        const button = e.target.closest(
            '[data-contextid="' + this.contextid + '"][data-action="publish"], [data-contextid="'
            + this.contextid + '"][data-action="mute"], [data-contextid="'
            + this.contextid + '"][data-action="unmute"], [data-contextid="'
            + this.contextid + '"][data-action="unpublish"]'
        );
        if (button) {
            const action = button.getAttribute('data-action'),
                type = button.getAttribute('data-type') || 'camera',
                transceiver = this.getTransceiver('audio');
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
                case 'mute':
                    if (transceiver) {
                        transceiver.sender.track.enabled = false;
                    }
                    break;
                case 'unmute':
                    if (transceiver) {
                        transceiver.sender.track.enabled = true;
                    }
                    break;
                case 'publish':
                    Log.debug(type);
                    if (type == 'display') {
                        this.shareDisplay();
                    } else {
                        this.shareCamera();
                    }

                    this.processStream([]);
                    break;
                case 'unpublish':
                    this.unpublish();
            }
        }

        return true;
    }

    /**
     * Set video source to user camera
     */
    shareCamera() {
        const videoInput = this.videoInput,
            currentCamera = this.currentCamera || Promise.resolve(null);

        this.videoInput = currentCamera.then(videoStream => {
            if (videoStream) {
                return videoStream;
            } else {
                const cameraInput = navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });

                this.currentCamera = cameraInput.catch(() => {
                    return currentCamera;
                });

                return cameraInput.then(videoStream => {
                    this.tracks = this.tracks || {};
                    videoStream.getTracks().forEach(track => {
                        this.tracks[track.id] = 'camera';
                    });

                    return videoStream;
                }).catch((e) => {
                    Log.debug(e);

                    return videoInput;
                });
            }
        });
    }

    /**
     * Set video source to display surface
     */
    shareDisplay() {
        const videoInput = this.videoInput,
            currentDisplay = this.currentDisplay || Promise.resolve(null),
            displayInput = navigator.mediaDevices.getDisplayMedia({
            video: true,
            audio: true
        });

        this.videoInput = displayInput.then(videoStream => {
            if (videoInput) {
                videoInput.then(videoStream => {
                    if (videoStream) {
                        videoStream.getTracks().forEach(track => {
                            Log.debug(track); //track.stop();
                        });
                    }
                    return videoStream;
                }).catch(Notification.exception);
            }
            this.tracks = this.tracks || {};
            videoStream.getTracks().forEach(track => {
                this.tracks[track.id] = 'display';
            });

            return videoStream;
        }).catch((e) => {
            Log.debug(e);

            return videoInput;
        });

        this.currentDisplay = displayInput.then(videoStream => {
            currentDisplay.then(videoStream => {
                if (videoStream) {
                    videoStream.getTracks().forEach(track => {
                        Log.debug('stop track');
                        Log.debug(track);
                        track.stop();
                    });
                }
            });

            return videoStream;
        }).catch((e) => {
            Log.debug(e);

            return currentDisplay;
        });
    }

    processStream(tracks) {
        this.videoInput.then(videoStream => {
            this.tracks = this.tracks || {};
            if (videoStream) {
                const audiotransceiver = this.getTransceiver('audio'),
                    videotransceiver = this.getTransceiver('video');
                videoStream.getVideoTracks().forEach(track => {
                    track.addEventListener('ended', () => {
                        if (this.selectedTrack != track.id) {
                            this.unpublish();
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
                    track.addEventListener('ended', () => {
                        if (this.selectedTrack != track.id) {
                            this.unpublish();
                        }
                    });
                    if (document.querySelector('.hidden[data-action="mute"][data-contextid="' + this.contextid + '"]')) {
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

            return videoStream;
        }).catch(Notification.exception);
    }
}

export default class VideoTime extends VideoTimeBase {
    initialize(contextid, token, peerid) {
        Log.debug("Initializing Video Time " + this.elementId);

        this.contextid = contextid;
        this.peerid = peerid;

        Ajax.call([{
            methodname: 'videotimeplugin_live_get_room',
            args: {contextid: contextid},
            done: (response) => {
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

                document.querySelector('[data-contextid="' + this.contextid + '"] .videotime-control').classList.remove('hidden');

                socket.subscribe(() => {
                    Ajax.call([{
                        methodname: 'videotimeplugin_live_get_feed',
                        args: {contextid: contextid},
                        done: (response) => {
                            const room = rooms[String(contextid)];
                            if (room.publish && room.publish.restart) {
                                if (response.feed == peerid) {
                                    this.unpublish();
                                }
                                room.publish = null;
                            }
                            this.subscribeTo(Number(response.feed));
                        },
                        fail: Notification.exception
                    }]);
                });
            },
            fail: Notification.exception
        }]);

        this.addListeners();

        return true;
    }

    /**
     * Register player events to respond to user interaction and play progress.
     */
    addListeners() {
        document.querySelector('body').removeEventListener('click', handleClick);
        document.querySelector('body').addEventListener('click', handleClick);
        return;
    }

    /**
     * Subscribe to feed
     *
     * @param {int} source Feed to subscribe
     */
    subscribeTo(source) {
        Log.debug(source);
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
        Log.debug(source);

        if (this.remoteFeed && !this.remoteFeed.creatingSubscription && !this.remoteFeed.restart) {
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

                if (room.publish  && this.remoteFeed.current == room.publish.feed) {
                    room.publish.handleClose();
                    room.publish = null;
                }
                this.remoteFeed.current = source;
                if (!source && this.remoteFeed) {
                    this.remoteFeed.handleClose();
                    this.remoteFeed = null;
                }
                Log.debug('[data-contextid="' + this.contextid + '"] img.poster-img');
                if (Number(source)) {
                    document.querySelectorAll('[data-contextid="' + this.contextid + '"] img.poster-img').forEach(img => {
                        img.classList.add('hidden');
                    });
                    document.querySelectorAll('[data-contextid="' + this.contextid + '"] video').forEach(video => {
                        video.classList.remove('hidden');
                    });
                } else {
                    document.querySelectorAll('[data-contextid="' + this.contextid + '"] img.poster-img').forEach(img => {
                        img.classList.remove('hidden');
                    });
                    document.querySelectorAll('[data-contextid="' + this.contextid + '"] video').forEach(video => {
                        video.classList.add('hidden');
                    });
                }
            }
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
            this.remoteFeed = new Subscribe(this.contextid, this.iceservers, this.roomid, this.server, this.peerid, source);
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
}

const handleClick = function(e) {
    const button = e.target.closest(
        '[data-roomid] [data-action="publish"], [data-roomid] [data-action="unpublish"],'
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
        if ((action == 'publish')  && (!room.publish || room.publish.restart)) {
            room.publish = new Publish(contextid, iceServers, roomid, server, peerid);
            if (type == 'display') {
                room.publish.shareDisplay();
            } else {
                room.publish.shareCamera();
            }
            room.publish.startConnection();
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
    register(pluginHandle) {
        // Try a registration
        return Ajax.call([{
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

    attachVideo(videoStream) {
        Janus.attachMediaStream(
            this.remoteVideo,
            videoStream
        );
    }
}
