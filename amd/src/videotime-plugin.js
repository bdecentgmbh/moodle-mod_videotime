
/**
 * @module mod_videotime/videotime-plugin
 */
 define(['mod_videotime/videotime'], function(VideoTime) {
    var VideoTimePlugin = function() {
        this.initialized = false;
    };

    /**
     * @param {VideoTime} videotime
     * @param {object} instance Prefetched VideoTime instance object.
     */
    VideoTimePlugin.prototype.initializeInfo = function(videotime, instance) {
        if (!(videotime instanceof VideoTime)) {
            throw new Error("Coding error. Invalid VideoTime passed to plugin. " + instance.name);
        }
        this.initialized = true;
    };

    return VideoTimePlugin;
 });
