export default class Song {

    constructor(title, artist, duration) {
        this.title = title;
        this.artist = artist;
        this.duration = duration;
        this.isPlaying = false;
    }

    start() {
        console.log('Song is playing');
        this.isPlaying = true;
    }

    stop() {
        console.log('Song is stopped');
        this.isPlaying = false;
    }

}
