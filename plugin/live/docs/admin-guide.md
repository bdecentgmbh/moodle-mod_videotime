# Video Time Live Player — Administrator guide

The Live player lets teachers broadcast their camera or screen in a Video Time activity. The video
connection is provided by the Deft response block (`block_deft`), which connects to a video server
(Janus) to carry the stream.

## Requirements

- The Deft response block. It is not declared as a dependency, so install it first.
- Moodle's VideoJS media player (`media_videojs`), which is declared as a dependency.

The Live player checks three settings of the Deft response block (*Site administration ▸ Plugins ▸
Blocks ▸ Deft response*):

| Setting | Needed for |
| --- | --- |
| **Enable updating** (a server type selected) | Everything. Without it, teachers are not offered **Live feed** and live activities show no player. |
| **Enable bridge** | The video room of a live activity. |
| **Enable video** | The sharing controls. Without it, teachers see a message instead of the controls. |

## Enabling the player

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time Plugins ▸ Manage Video
Time extensions*: check that the Live player is enabled (eye icon in **Hide/Show**).

## Settings

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time Plugins ▸ Video Time
Live Player*

**Poster image**: shown in live activities while nobody is broadcasting. PNG, JPG, GIF, WebP, TIFF
and SVG files are accepted. Without it, the Video Time icon is shown.

The page also has default, forced and advanced embed options like the other players (**Autoplay**,
**Responsive**, **Height**, **Width**, **Controls**, **Muted**, **Plays inline**). By default,
Height, Muted, Plays inline and Width are advanced and nothing is forced.

## Capabilities

| Capability | Default | Allows |
| --- | --- | --- |
| `videotimeplugin/live:sharevideo` | Teacher, Non-editing teacher | Broadcasting a feed |
| `videotimeplugin/live:moderate` | Teacher, Non-editing teacher | Replacing a feed that another user is broadcasting |

Two capabilities of the Deft response block are also checked in the activity:
`block/deft:moderate` to see the **Shared video** block with the controls, and
`block/deft:sharevideo` to join the video room as a broadcaster. A teacher needs all of them to
broadcast. Viewing a broadcast needs `mod/videotime:view`.

## Scheduled task

**Cleanup task for Video Time Live player** (`\videotimeplugin_live\task\cleanup`) runs once a day
and deletes the connection records of closed connections and expired sessions.

## Privacy

For every connection the player keeps a temporary record with the user, whether the connection is
closed, the mute state and its times. The cleanup task removes them.
