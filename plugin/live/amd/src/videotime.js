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
                id: Number(this.peerid),
                plugin: pluginHandle.plugin,
                room: this.roomid,
                ptype: this.ptype == 'publish',
                session: pluginHandle.session.getSessionId()
            },
            contextid: this.contextid,
            fail: Notification.exception,
            methodname: 'videotimeplugin_live_join_room'
        }])[0];
    }

    getTransceiver(id) {
        let result = null;

        if (
            this.videoroom.webrtcStuff.pc
            && this.videoroom.webrtcStuff.pc.iceConnectionState == 'connected'
        ) {

            Log.debug(this.videoroom.webrtcStuff.pc.getTransceivers());
            this.videoroom.webrtcStuff.pc.getTransceivers().forEach(transceiver => {
                const sender = transceiver.sender;
                if (
                    sender.track
                    && sender.track.id
                    && sender.track.id == id
                    && this.tracks[sender.track.id]
                ) {
                    result = transceiver;
                }
            });
        }

        return result;
    }

    publishFeed() {
        if (
            this.videoroom.webrtcStuff.pc
            && this.videoroom.webrtcStuff.pc.iceConnectionState == 'connected'
        ) {
            setTimeout(() => {
                this.videoroom.webrtcStuff.pc.getTransceivers().forEach(transceiver => {
                    const sender = transceiver.sender;
                    if (
                        sender.track
                        && this.selectedTrack
                        && (sender.track.id == this.selectedTrack.id)
                    ) {
                        const message = JSON.stringify({
                            feed: Number(this.peerid),
                            mid: transceiver.mid
                        });
                        this.videoroom.data({
                            text: message,
                            error: Log.debug
                        });
                    }
                });
                return Ajax.call([{
                    args: {
                        id: Number(this.peerid),
                        room: this.roomid,
                    },
                    contextid: this.contextid,
                    fail: Notification.exception,
                    methodname: 'videotimeplugin_live_publish_feed'
                }])[0];
            });
        }
    }

    unpublish() {
        if (this.videoInput) {
            this.videoInput.then(videoStream => {
                this.videoInput = null;
                return videoStream;
            }).catch(Notification.exception);
            this.videoroom.send({
                message: {
                    request: 'unpublish'
                }
            });
        }
        if (this.currentCamera) {
            this.currentCamera = this.currentCamera.then(videoStream => {
                if (videoStream) {
                    videoStream.getVideoTracks().forEach(track => {
                        track.stop();
                    });
                }

                return null;
            }).catch(Notification.exception);
        }
        if (this.currentDisplay) {
            this.currentDisplay = this.currentDisplay.then(videoStream => {
                if (videoStream) {
                    videoStream.getVideoTracks().forEach(track => {
                        track.stop();
                    });
                }

                return null;
            }).catch(Notification.exception);
        }
        document.querySelectorAll(
            '[data-contextid="' + this.contextid + '"][data-action="publish"]'
        ).forEach(button => {
            button.classList.remove('hidden');
        });
        //document.querySelectorAll(
            //'[data-contextid="' + this.contextid + '"][data-action="unpublish"]'
        //).forEach(button => {
            //button.classList.add('hidden');
        //});
    }

    onLocalTrack(track, on) {
        const remoteStream = new MediaStream([track]);
        if (!on) {
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
            '[data-contextid="' + this.contextid + '"][data-action="publish"],  [data-contextid="'
                + this.contextid + '"][data-action="unpublish"]'
        );
        if (button) {
            const action = button.getAttribute('data-action'),
                type = button.getAttribute('data-type') || 'camera';
            e.stopPropagation();
            e.preventDefault();
            document.querySelectorAll(
                '[data-region="deft-venue"] [data-action="publish"],  [data-region="deft-venue"] [data-action="unpublish"]'
            ).forEach(button => {
                if ((button.getAttribute('data-action') != action) || (button.getAttribute('data-type') != type)) {
                    button.classList.remove('hidden');
                }
            });
            switch (action) {
                case 'publish':
                    Log.debug(type);
                    if (type == 'display') {
                        this.shareDisplay();
                    } else {
                        this.shareCamera();
                    }

                    this.videoInput.then(videoStream => {
                        const tracks = [];
                        this.tracks = this.tracks || {};
                        if (videoStream) {
                            Log.debug(videoStream.getVideoTracks());
                            videoStream.getVideoTracks().forEach(track => {
                                const transceiver = this.getTransceiver(track.id);
                                if (!transceiver) {
                                    tracks.push({
                                        type: 'video',
                                        capture: track,
                                        recv: false
                                    });
                                    this.selectedTrack = track;
                                    this.tracks[track.id] = type;
                                    Log.debug('New track');
                                } else {
                                    const message = JSON.stringify({
                                        feed: Number(this.peerid),
                                        mid: transceiver.mid
                                    });
                                    this.videoroom.data({
                                        text: message,
                                        error: Log.debug
                                    });
                                    this.selectedTrack = track.id;
                                    this.publishFeed();
                                }
                            });
                            videoStream.getAudioTracks().forEach(track => {
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
                    break;
                case 'unpublish':
                    if (this.videoInput) {
                        this.videoInput.then(videoStream => {
                            if (videoStream) {
                                videoStream.getVideoTracks().forEach(track => {
                                    track.stop();
                                });
                            }
                            this.videoInput = null;

                            return videoStream;
                        }).catch(Notification.exception);
                    }
                    this.videoroom.send({
                        message: {
                            request: 'unpublish'
                        }
                    });
                    return Ajax.call([{
                        args: {
                            id: Number(this.peerid),
                            publish: false,
                            room: this.roomid
                        },
                        contextid: this.contextid,
                        fail: Notification.exception,
                        methodname: 'videotimeplugin_publish_feed'
                    }])[0];
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
                    audio: false
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
            audio: false
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
        Log.debug(this.peerid);

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
                this.remoteFeed.videoroom.send({message: update});
                if (this.remoteFeed.current == this.peerid) {
                    const room = rooms[String(this.contextid)];
                    room.publish.unpublish();
                }
                this.remoteFeed.current = source;
                Log.debug('[data-contextid="' + this.contextid + '"] img.poster-img');
                if (source) {
                    document.querySelectorAll('[data-contextid="' + this.contextid + '"] img.poster-img').forEach(img => {
                        img.classList.add('hidden');
                    });
                    document.querySelectorAll('[data-contextid="' + this.contextid + '"] video').forEach(img => {
                        img.classList.remove('hidden');
                    });
                } else {
                    document.querySelectorAll('[data-contextid="' + this.contextid + '"] img.poster-img').forEach(img => {
                        img.classList.remove('hidden');
                    });
                    document.querySelectorAll('[data-contextid="' + this.contextid + '"] video').forEach(img => {
                        img.classList.add('hidden');
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
    const button = e.target.closest('[data-roomid] [data-action="publish"], [data-roomid] [data-action="unpublish"]');
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
        if (action == 'unpublish') {
            Ajax.call([{
                args: {
                    id: Number(peerid),
                    room: roomid,
                    publish: false
                },
                contextid: contextid,
                fail: Notification.exception,
                methodname: 'videotimeplugin_live_publish_feed'
            }]);
        } else if (!room.publish || room.publish.restart) {
            room.publish = new Publish(contextid, iceServers, roomid, server, peerid);
            if (type == 'display') {
                room.publish.shareDisplay();
            } else {
                room.publish.shareCamera();
            }
            room.publish.startConnection();
        } else {
            room.publish.handleClick(e);
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
                id: Number(this.peerid),
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
}
