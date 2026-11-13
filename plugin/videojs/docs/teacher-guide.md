# Video Time VideoJS Player — Teacher guide

The VideoJS player plays everything in a Video Time activity that is not a Vimeo video: YouTube
videos, video and audio files on the web, and files you upload to the activity.

## Choosing the media

In the activity settings, use one of these, not both:

- **Video URL**: a YouTube link (`youtube.com/watch?v=…`, `youtu.be/…`, also
  `youtube-nocookie.com`), a link to an HLS stream (`.m3u8`), or a link to a video or audio file.
- **Media file**: upload one audio or video file. Leave **Video URL** empty.

Accepted file formats are MP4, WebM, Ogg, MPEG and QuickTime video, MP3, Ogg, WAV and FLAC audio,
and HLS playlists (`.m3u8`). The form rejects other links and file types. If you fill in both
fields, it shows "Use a file or url, not both". When you save a link for an activity that had an
uploaded file, the file is removed.

## Creating an activity by drag and drop

With editing turned on, drag a video file onto a section of the course page and choose **Create
Video Time activity**. The activity is created with the file as its media file and is named after
the file. The course page offers this for `.avi`, `.mp4`, `.m3u8`, `.mkv`, `.oga`, `.ogg`, `.ogv`
and `.webm` files.

## Embed options

Open the activity and choose **Embed options** in its navigation. The page is available to users
who may manage activities in the course.

| Option | Effect |
| --- | --- |
| **Responsive** | The player adapts to the page or screen size. While it is checked, **Height** and **Width** are disabled. |
| **Height**, **Width** | A fixed player size. Used only when **Responsive** is unchecked and both values are set. |
| **Autoplay** | Starts the video automatically. Some devices and browsers block this. |
| **Controls** | Shows the player controls. |
| **Loop** | Starts the video again when it ends. |
| **Muted** | Mutes the video when it loads. Some browsers only autoplay muted videos. |
| **Plays inline** | Plays the video inline on mobile devices instead of fullscreen. |
| **Speed** | Lets viewers choose a playback rate between 0.5 and 2. |
| **Poster file** | An image shown before the video starts. |

With Video Time Pro, an activity that uses a Media Time resource also has **Display Media Time
tracks**.

Your administrator can lock options for the whole site. A locked option is replaced by a line such
as "Controls is globally forced to: Yes". Options that your administrator marked as advanced appear
after **Show more...**.

## What participants see

Videos play in the VideoJS player, with the activity's notes below. The activity's visible text
tracks are loaded into the player. Audio files play in an audio player; the image shown is the
poster file, or the site's default audio image when the activity has no poster file.
