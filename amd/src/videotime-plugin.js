/**
 * @module mod_videotime/videotime-plugin
 */
define(['mod_videotime/videotime'], function(VideoTime) {
    var VideoTimePlugin = function(name) {
        this.name = name;
        this.initialized = false;
    };

    VideoTimePlugin.prototype.getName = function() {
        return this.name;
    };

    /**
     * @param {VideoTime} videotime
     * @param {object} instance Prefetched VideoTime instance object.
     */
    VideoTimePlugin.prototype.initialize = function(videotime, instance) {
        if (!(videotime instanceof VideoTime)) {
            throw new Error("Coding error. Invalid VideoTime passed to plugin. " + instance.name);
        }
        this.initialized = true;
    };

    return VideoTimePlugin;
 });
