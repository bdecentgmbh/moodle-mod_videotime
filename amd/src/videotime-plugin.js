
import VideoTime from "mod_videotime/videotime";

export default class VideoTimePlugin {
    constructor(name) {
        this.name = name;
        this.initialized = false;
    }

    getName() {
        return this.name;
    }

    /**
     * @param {VideoTime} videotime
     * @param {object} instance Prefetched VideoTime instance object.
     */
    initialize(videotime, instance) {
        if (!(videotime instanceof VideoTime)) {
            throw new Error("Coding error. Invalid VideoTime passed to plugin. " + instance.name);
        }

        this.initialized = true;
    }
}
