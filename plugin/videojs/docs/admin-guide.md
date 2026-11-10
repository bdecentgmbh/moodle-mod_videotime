# Video Time VideoJS Player — Administrator guide

The VideoJS player plays YouTube videos, media files on the web, uploaded media files and HLS
streams in Video Time activities. It uses Moodle's VideoJS media player (`media_videojs`), which it
requires. It is enabled when it is installed.

## Enabling and disabling

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time Plugins ▸ Manage Video
Time extensions*: use the eye icon in **Hide/Show**.

## Settings

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time Plugins ▸ Video Time
VideoJS Player*

### Default Settings

The default embed options of new activities that use this player. Teachers change them per
activity on the activity's *Embed options* page.

| Option | Default |
| --- | --- |
| Autoplay | Yes |
| Responsive | Yes |
| Height, Width | Empty |
| Controls | Yes |
| Loop | No |
| Muted | No |
| Plays inline | Yes |
| Speed | Yes |

**Default audio image**: the image shown for audio files when the activity has no poster file.
PNG, JPG, GIF, WebP, TIFF and SVG files are accepted. Without it, a generic audio icon is shown.

### Forced settings

The options selected in **Forced settings** take their default value in every activity that uses
this player, and teachers cannot change them. Nothing is forced by default.

### Advanced settings

The options selected in **Advanced settings** are shown only after **Show more...** on the *Embed
options* page. By default these are Height, Muted, Loop, Plays inline and Width.

## Drag and drop

When editing a course, users can drop `.avi`, `.mp4`, `.m3u8`, `.mkv`, `.oga`, `.ogg`, `.ogv` and
`.webm` files onto the course page to create Video Time activities.
