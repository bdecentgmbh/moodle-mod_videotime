/**
 * Manage venue connections
 *
 * @module     videotimetab_venue/venue_manager
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";
import Fragment from "core/fragment";
import {get_string as getString} from 'core/str';
import Janus from 'block_deft/janus-gateway';
import Log from "core/log";
import Notification from "core/notification";
import Socket from "videotimeplugin_live/socket";
import * as Toast from 'core/toast';
import VenueManagerBase from "block_deft/janus_venue";

var rooms = {},
    stereo = false;

export default class JanusManager extends VenueManagerBase {
    /**
     * Add event listeners
     */
    addListeners() {

        document.querySelector('body').removeEventListener('click', this.handleMuteButtons.bind(this));
        document.querySelector('body').addEventListener('click', this.handleMuteButtons.bind(this));

        document.querySelector('body').removeEventListener('click', this.handleRaiseHand.bind(this));
        document.querySelector('body').addEventListener('click', this.handleRaiseHand.bind(this));

        document.querySelector('body').removeEventListener('click', this.closeConnections.bind(this));
        document.querySelector('body').addEventListener('click', this.closeConnections.bind(this));

        window.onbeforeunload = this.closeConnections.bind(this);

        this.audioInput = Promise.resolve(null);

        this.socket = new Socket(this.contextid, this.token);
        this.socket.subscribe(() => {
            this.sendSignals();
        });
    }


    /**
     * Start to establish the peer connections
     */
    startConnection() {
        const contextid = this.contextid,
            peerid = this.peerid,
            room = rooms[contextid];
        if (!this.started) {
            Ajax.call([{
                methodname: 'videotimeplugin_live_get_room',
                args: {contextid: contextid},
                done: (response) => {
                    this.iceservers = JSON.parse(response.iceservers);
                    this.roomid = Number(response.roomid);
                    this.server = response.server;

                    rooms[String(contextid)] = {
                        contextid: contextid,
                        manager: this,
                        peerid: peerid,
                        roomid: response.roomid,
                        server: response.server,
                        iceServers: JSON.parse(response.iceservers)
                    };
                    this.startConnection();
                },
                fail: Notification.exception
            }]);
            this.started = true;
        }
        if (!room) {
            return;
        }
        this.transactions = {};

        document.querySelector('body').removeEventListener('venueclosed', this.handleClose.bind(this));
        document.querySelector('body').addEventListener('venueclosed', this.handleClose.bind(this));

        document.querySelector('body').removeEventListener('click', handleClick);
        document.querySelector('body').addEventListener('click', handleClick);

        if (!document.querySelector('[data-contextid="' + this.contextid + '"] .hidden[data-action="join"]')) {
            return;
        }

        if (document.querySelector('[data-contextid="' + this.contextid + '"] .hidden[data-action="unmute"]')) {
        this.audioInput = navigator.mediaDevices.getUserMedia({
            audio: {
                autoGainControl: this.autogaincontrol,
                echoCancellation: this.echocancellation,
                noiseSuppression: this.noisesuppression,
                sampleRate: this.samplerate
            },
            video: false
        }).catch(() => {
            Ajax.call([{
                args: {
                    mute: true,
                    "status": false
                },
                fail: Notification.exception,
                methodname: 'videotimetab_venue_settings'
            }]);

            return false;
        });
        this.audioInput.then(this.monitorVolume.bind(this)).catch(Log.debug);
        } else {
            this.audioInput = Promise.resolve(null);
        }

        // Initialize the library (all console debuggers enabled)
        Janus.init({
            debug: "none", callback: () => {
                // Create session.
                this.janus = new Janus(
                    {
                        server: this.server,
                        iceServers: this.iceServers,
                        success: () => {
                            // Attach audiobridge plugin.
                            this.janus.attach(
                                {
                                    plugin: "janus.plugin.audiobridge",
                                    opaqueId: "audioroom-" + Janus.randomString(12),
                                    success: pluginHandle => {
                                        this.audioBridge = pluginHandle;
                                        Log.debug(pluginHandle.session.getSessionId());
                                        this.register(pluginHandle);
                                    },
                                    error: function(error) {
                                        Janus.error("  -- Error attaching plugin...", error);
                                        Notification.alert('', "Error attaching plugin... " + error);
                                    },
                                    onmessage: this.onMessage.bind(this),
                                    onremotetrack: (track, mid, on, metadata) => {
                                        Janus.debug(
                                            "Remote track (mid=" + mid + ") " +
                                            (on ? "added" : "removed") +
                                            (metadata ? " (" + metadata.reason + ") " : "") + ":", track
                                        );
                                        if (this.remoteStream || track.kind !== "audio") {
                                            return;
                                        }
                                        if (!on) {
                                            // Track removed, get rid of the stream and the rendering
                                            this.remoteStream = null;
                                            return;
                                        }
                                        this.remoteStream = new MediaStream([track]);
                                        Janus.attachMediaStream(document.getElementById('roomaudio'), this.remoteStream);
                                    }
                                }
                            );
                            this.janus.attach(
                                {
                                    plugin: "janus.plugin.textroom",
                                    opaqueId: "textroom-" + Janus.randomString(12),
                                    success: pluginHandle => {
                                        this.textroom = pluginHandle;
                                        Janus.log("Plugin attached! (" + this.textroom.getPlugin()
                                            + ", id=" + this.textroom.getId() + ")");
                                        // Setup the DataChannel
                                        const body = {request: "setup"};
                                        Janus.debug("Sending message:", body);
                                        this.textroom.send({message: body});
                                    },
                                    error: function(error) {
                                        Notification.alert('', error);
                                        Janus.error("  -- Error attaching plugin...", error);
                                    },
                                    onmessage: (msg, jsep) => {
                                        Janus.debug(" ::: Got a message :::", msg);
                                        if (msg.error) {
                                            Notification.alert(msg.error_code, msg.error);
                                        }

                                        if (jsep) {
                                            // Answer
                                            this.textroom.createAnswer(
                                                {
                                                    jsep: jsep,
                                                    // We only use datachannels
                                                    tracks: [
                                                        {type: 'data'}
                                                    ],
                                                    success: (jsep) => {
                                                        Janus.debug("Got SDP!", jsep);
                                                        const body = {request: "ack"};
                                                        this.textroom.send({message: body, jsep: jsep});
                                                    },
                                                    error: function(error) {
                                                        Janus.error("WebRTC error:", error);
                                                    }
                                                }
                                            );
                                        }
                                    },
                                    // eslint-disable-next-line no-unused-vars
                                    ondataopen: (label, protocol) => {
                                        const transaction = Janus.randomString(12),
                                            register = {
                                                textroom: "join",
                                                transaction: transaction,
                                                room: this.roomid,
                                                username: String(this.peerid),
                                                display: '',
                                            };
                                        this.textroom.data({
                                            text: JSON.stringify(register),
                                            error: function(reason) {
                                                Notification.alert('Error', reason);
                                            }
                                        });
                                    },
                                    ondata: (data) => {
                                        Janus.debug("We got data from the DataChannel!", data);
                                        const message = JSON.parse(data),
                                            event = message.textroom,
                                            transaction = message.transaction;
                                        if (transaction && this.transactions[transaction]) {
                                            this.transactions[transaction](message);
                                            delete this.transactions[transaction];
                                        }

                                        if (event === 'message' && message.from != this.peerid) {
                                            this.handleMessage(message.from, {data: message.text});
                                        }
                                        if (event === 'error') {
                                            Log.debug(message);
                                        }
                                        if (event === 'join') {
                                            this.sendMessage(JSON.stringify({
                                                "raisehand": !!document.querySelector(
                                                    '[data-peerid="' + this.peerid + '"] a.hidden[data-action="raisehand"]'
                                                )
                                            }));
                                        }
                                    }
                                }
                            );
                        },
                        error: (error) => {
                            getString('serverlost', 'block_deft').done((message) => {
                                Toast.add(message, {'type': 'info'});
                            });
                            Log.debug(error);
                            this.restart = true;
                        },
                        destroyed: function() {
                            window.close();
                        }
                    }
                );
            }
        });
    }

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
                session: pluginHandle.session.getSessionId(),
            },
            contextid: this.contextid,
            fail: Notification.exception,
            methodname: 'videotimeplugin_live_join_room'
        }])[0];
    }

    /**
     * Handle plugin message
     *
     * @param {object} msg msg
     * @param {string} jsep
     */
    onMessage(msg, jsep) {
        const event = msg.audiobridge;
        Log.debug(msg);
        if (event) {
            if (event === "joined") {
                // Successfully joined, negotiate WebRTC now
                if (msg.id) {
                    Janus.log("Successfully joined room " + msg.room + " with ID " + this.peerid);
                    Log.debug("Successfully joined room " + msg.room + " with ID " + this.peerid);
                    if (!this.webrtcUp) {
                        this.webrtcUp = true;
                        this.audioInput.then(audioStream => {
                            // Publish our stream.
                            const tracks = [];
                            if (audioStream) {
                                audioStream.getAudioTracks().forEach(track => {
                                    tracks.push({
                                        type: 'audio',
                                        capture: track,
                                        recv: true
                                    });
                                });
                            } else {
                                tracks.push({
                                    type: 'audio',
                                    capture: true,
                                    recv: true
                                });
                            }
                            this.audioBridge.createOffer({
                                // We only want bidirectional audio
                                tracks: tracks,
                                customizeSdp: function(jsep) {
                                    if (stereo && jsep.sdp.indexOf("stereo=1") == -1) {
                                        // Make sure that our offer contains stereo too
                                        jsep.sdp = jsep.sdp.replace("useinbandfec=1", "useinbandfec=1;stereo=1");
                                    }
                                },
                                success: (jsep) => {
                                    Janus.debug("Got SDP!", jsep);
                                    const publish = {request: "configure", muted: false};
                                    this.audioBridge.send({message: publish, jsep: jsep});
                                },
                                error: function(error) {
                                    Janus.error("WebRTC error:", error);
                                    Notification.alert("WebRTC error... ", error.message);
                                }
                            });

                            return audioStream;
                        }).catch(Notification.exception);
                    }
                }
                // Any room participant?
                if (msg.participants) {
                    this.updateParticipants(msg.participants);
                }
            } else if (event === "left") {
                document.querySelector('[data-contextid="' + this.contextid + '"] [data-action="join"]').classList.remove('hidden');
                document.querySelector('[data-contextid="' + this.contextid + '"] [data-action="leave"]').classList.add('hidden');
            } else if (event === "destroyed") {
                // The room has been destroyed
                Janus.warn("The room has been destroyed!");
                Notification.alert('', "The room has been destroyed");
            } else if (event === "event") {
                if (msg.participants) {
                    this.updateParticipants(msg.participants);
                } else if (msg.error) {
                    if (msg.error_code === 485) {
                        // This is a "no such room" error: give a more meaningful description
                        Notification.alert(
                            "<p>Room <code>" + this.roomid + "</code> is not configured."
                        );
                    } else {
                        Notification.alert(msg.error_code, msg.error);
                    }
                    return;
                }
                if (msg.leaving) {
                    // One of the participants has gone away?
                    const leaving = msg.leaving;
                    Janus.log(
                        "Participant left: " + leaving
                    );
                    document.querySelectorAll(
                        '[data-region="venue-participants"] [data-peerid="' + leaving + '"]'
                    ).forEach(peer => {
                        peer.remove();
                    });
                }
            }
        }
        if (jsep) {
            Janus.debug("Handling SDP as well...", jsep);
            this.audioBridge.handleRemoteJsep({jsep: jsep});
        }
    }

    processSignal() {
        return;
    }

    /**
     * Update participants display for audio bridge
     *
     * @param {array} list List of participants returned by plugin
     */
    updateParticipants(list) {
        Janus.debug("Got a list of participants:", list);
        for (const f in list) {
            const id = list[f].id,
                display = list[f].display,
                setup = list[f].setup,
                muted = list[f].muted;
            Janus.debug("  >> [" + id + "] " + display + " (setup=" + setup + ", muted=" + muted + ")");
            if (
                !document.querySelector('[data-region="venue-participants"] [data-peerid="' + id + '"]')
                && Number(this.peerid) != Number(id)
            ) {
                // Add to the participants list
                this.peerAudioPlayer(id);
            }
        }
    }

    /**
     * Transfer signals with signal server
     */
    sendSignals() {

        if (this.throttled || !navigator.onLine) {
            return;
        }

        const time = Date.now();
        if (this.lastUpdate + 200 > time) {
            this.throttled = true;
            setTimeout(() => {
                this.throttled = false;
            }, this.lastUpdate + 250 - time);
            this.sendSignals();
            return;
        }
        this.lastUpdate = time;

        Ajax.call([{
            args: {
                contextid: this.contextid
            },
            contextid: this.contextid,
            done: response => {
                response.settings.forEach(peer => {
                    if (peer.id == Number(this.peerid)) {
                        if (peer.status) {
                            // Release microphone.
                            clearInterval(this.meterId);
                            this.audioInput.then(audioStream => {
                                if (audioStream) {
                                    audioStream.getAudioTracks().forEach(track => {
                                        track.stop();
                                    });
                                }
                                return audioStream;
                            }).catch(Log.debug);

                            // Close connections.
                            this.janus.destroy();

                            document.querySelectorAll(
                                '[data-region="deft-venue"] [data-peerid="' + this.peerid
                                + '"]'
                            ).forEach(venue => {
                                const e = new Event('venueclosed', {bubbles: true});
                                venue.dispatchEvent(e);
                            });

                            this.socket.disconnect();

                            window.close();
                            return;
                        }
                        this.mute(peer.mute);
                    }
                    document.querySelectorAll(
                        '[data-contextid="' + this.contextid + '"] [data-peerid="' + peer.id +
                        '"] [data-action="mute"], [data-contextid="' + this.contextid + '"] [data-peerid="' + peer.id
                            + '"] [data-action="unmute"]'
                    ).forEach(button => {
                        if (peer.mute == (button.getAttribute('data-action') == 'mute')) {
                            button.classList.add('hidden');
                        } else {
                            button.classList.remove('hidden');
                        }
                    });
                });
                document.querySelectorAll('[data-region="venue-participants"] [data-peerid]').forEach(peer => {
                    if (!response.peers.includes(Number(peer.getAttribute('data-peerid')))) {
                        peer.remove();
                    }
                });
                if (!response.peers.includes(Number(this.peerid))) {
                    return;
                }
                if (this.restart) {
                    getString('reconnecting', 'block_deft').done((message) => {
                        Toast.add(message, {'type': 'info'});
                    });
                    this.restart = false;
                    this.startConnection();
                }
            },
            fail: Notification.exception,
            methodname: 'videotimetab_venue_status'
        }]);
    }

    /**
     * Send a message through data channel to peers
     *
     * @param {string} text
     */
    sendMessage(text) {
        if (text && text !== "" && this.textroom) {
            const message = {
                textroom: "message",
                transaction: Janus.randomString(12),
                room: this.roomid,
                text: text
            };
            this.textroom.data({
                text: JSON.stringify(message),
                error: Log.debug,
            });
        }
    }

    /**
     * Subscribe to feed
     *
     */
    subscribeTo() {
        return;
    }

    /**
     * Close connection when peer removed
     */
    handleClose() {
        if (this.janus) {
            this.janus.destroy();
            this.janus = null;
        }

        document.querySelector('body').removeEventListener('click', handleClick);


        if (this.remoteFeed && this.remoteFeed.janus) {
            this.remoteFeed.janus.destroy();
            this.remoteFeed = null;
        }
    }

    /**
     * Return audio player for peer
     *
     * @param {int} peerid Peer id
     * @returns {Promise} Resolve to audio player node
     */
    peerAudioPlayer(peerid) {
        const usernode = document.querySelector('[data-region="venue-participants"] div[data-peerid="' + peerid + '"] audio');
        if (usernode) {
            return Promise.resolve(usernode);
        } else {
            const node = document.createElement('div');
            node.setAttribute('data-peerid', peerid);
            if (document.querySelector('body#page-blocks-deft-venue')) {
                node.setAttribute('class', 'col col-12 col-sm-6 col-md-4 col-lg-3 p-2');
            } else {
                node.setAttribute('class', 'col col-12 col-sm-6 col-md-4 p-2');
            }
            window.setTimeout(() => {
                node.querySelectorAll('img.card-img-top').forEach(image => {
                    image.setAttribute('height', null);
                    image.setAttribute('width', null);
                });
            });
            return Fragment.loadFragment(
                'videotimetab_venue',
                'venue',
                this.contextid,
                {
                    peerid: peerid
                }
            ).done((userinfo) => {
                if (!document.querySelector('[data-region="venue-participants"] div[data-peerid="' + peerid + '"] audio')) {
                    document.querySelector('[data-region="venue-participants"]').appendChild(node);
                    node.innerHTML = userinfo;
                }
            }).then(() => {
                return document.querySelector('[data-region="venue-participants"] div[data-peerid="' + peerid + '"] audio');
            }).catch(Notification.exception);
        }
    }

    /**
     * Handle click for mute
     *
     * @param {Event} e Button click
     */
    handleMuteButtons(e) {
        const button = e.target.closest(
            'a[data-action="mute"], a[data-action="unmute"]'
        );
        if (button) {
            const action = button.getAttribute('data-action'),
                peerid = button.closest('[data-peerid]').getAttribute('data-peerid');
            e.stopPropagation();
            e.preventDefault();
            if (!button.closest('[data-region="venue-participants"]')) {
                this.audioInput.then(audioStream => {
                    if (audioStream) {
                        Ajax.call([{
                            args: {
                                contextid: this.contextid,
                                mute: action == 'mute',
                                "status": false
                            },
                                fail: Notification.exception,
                            methodname: 'videotimetab_venue_settings'
                        }]);
                    } else if (action == 'unmute') {
                        this.audioInput = navigator.mediaDevices.getUserMedia({
                            audio: {
                                autoGainControl: this.autogaincontrol,
                                echoCancellation: this.echocancellation,
                                noiseSuppression: this.noisesuppression,
                                sampleRate: this.samplerate
                            },
                            video: false
                        }).then(audioStream => {

                            Ajax.call([{
                                args: {
                                    mute: false,
                                    "status": false
                                },
                                fail: Notification.exception,
                                methodname: 'videotimetab_venue_settings'
                            }]);

                            this.monitorVolume(audioStream);

                            return audioStream;
                        }).catch(Log.debug);
                    }

                    return audioStream;
                }).catch(Notification.exception);
            } else {
                Ajax.call([{
                    args: {
                        contextid: this.contextid,
                        mute: true,
                        peerid: peerid,
                        "status": false
                    },
                    fail: Notification.exception,
                    methodname: 'videotimetab_venue_settings'
                }]);
            }
            button.closest('[data-peerid]').querySelectorAll('[data-action="mute"], [data-action="unmute"]').forEach(option => {
                if (option.getAttribute('data-action') == action) {
                    option.classList.add('hidden');
                } else {
                    option.classList.remove('hidden');
                }
            });
        }
    }

    /**
     * Handle hand raise buttons
     *
     * @param {Event} e Click event
     */
    handleRaiseHand(e) {
        const button = e.target.closest(
            '[data-action="raisehand"], [data-action="lowerhand"]'
        );
        if (button && !button.closest('[data-region="venue-participants"]')) {
            const action = button.getAttribute('data-action');
            e.stopPropagation();
            e.preventDefault();
            if (action == 'raisehand') {
                document.querySelector('body').classList.add('videotimetab_raisehand');
            } else {
                document.querySelector('body').classList.remove('videotimetab_raisehand');
            }
            document.querySelectorAll('a[data-action="raisehand"], a[data-action="lowerhand"]').forEach(button => {
                if (button.getAttribute('data-action') == action) {
                    button.classList.add('hidden');
                } else {
                    button.classList.remove('hidden');
                }
            });
            Ajax.call([{
                args: {
                    contextid: this.contextid,
                    "status": action == 'raisehand'
                },
                    fail: Notification.exception,
                methodname: 'videotimetab_venue_raise_hand'
            }]);
            this.sendMessage(JSON.stringify({"raisehand": action == 'raisehand'}));
        }
    }

    /**
     * Send a message through data channel to peers
     *
     * @param {string} text
     */
    sendMessage(text) {
        if (
            text
            &&
            text !== ""
            && this.textroom
            && document.querySelector('[data-contextid="' + this.contextid + '"] .hidden[data-action="join"]')
        ) {
            const message = {
                textroom: "message",
                transaction: Janus.randomString(12),
                room: this.roomid,
                text: text
            };
            this.textroom.data({
                text: JSON.stringify(message),
                error: Log.debug,
            });
        }
    }
}

/**
 * Handle click event
 *
 * @param {Event} e Click event
 */
const handleClick = function(e) {
    const button = e.target.closest('[data-contextid] a[data-action="join"], [data-contextid] a[data-action="leave"]');
    if (button) {
        const action = button.getAttribute('data-action'),
            contextid = button.closest('[data-contextid]').getAttribute('data-contextid'),
            room = rooms[contextid];
        e.stopPropagation();
        e.preventDefault();

        document.querySelectorAll(
            '[data-contextid] a[data-action="join"], [data-contextid] a[data-action="leave"]'
        ).forEach(button => {
            if (contextid == button.closest('[data-contextid]').getAttribute('data-contextid')) {
                if (action == button.getAttribute('data-action')) {
                    button.classList.add('hidden');
                } else {
                    button.classList.remove('hidden');
                }
            }
        });

        if (action == 'leave') {
            const transaction = Janus.randomString(12),
                leave = {
                    textroom: "leave",
                    transaction: transaction,
                    room: room.manager.roomid
                };
            Ajax.call([{
                args: {
                    contextid: room.manager.contextid,
                    mute: true,
                    "status": true
                },
                fail: Notification.exception,
                methodname: 'videotimetab_venue_settings'
            }]);
            room.manager.audioBridge.send({
                message: {
                    request: 'leave'
                }
            });
            room.manager.textroom.data({
                text: JSON.stringify(leave),
                error: function(reason) {
                    Notification.alert('Error', reason);
                }
            });
        } else if (room) {
            if (room.manager.audioBridge) {
                const transaction = Janus.randomString(12),
                    join = {
                        textroom: "join",
                        transaction: transaction,
                        room: Number(room.manager.roomid),
                        display: '',
                        username: String(room.manager.peerid)
                    };
                room.manager.textroom.data({
                    text: JSON.stringify(join),
                    error: function(reason) {
                        Notification.alert('Error', reason);
                    }
                });
                room.manager.register(room.manager.audioBridge).then(() => {
                    const transaction = Janus.randomString(12),
                        configure = {
                            audiobridge: "configure",
                            mute: false,
                            transaction: transaction
                        };
                    room.manager.audiobridge.send({
                        message: JSON.stringify(configure),
                        error: function(reason) {
                            Notification.alert('Error', reason);
                        }
                    });
                });
            } else {
                setTimeout(() => {
                    room.manager.startConnection();
                });
            }
        }
    }
};
