# Video Time Live Player — Developer guide

The Live player (`videotimeplugin_live`) builds on the Deft response block (`block_deft`): its
PHP classes extend `\block_deft\socket` and `\block_deft\janus_room`, its JavaScript extends
`block_deft/publish` and `block_deft/subscribe` and uses `block_deft/janus-gateway`. The block is
not declared in `version.php`, so code paths check its settings (`enableupdating`, `enablebridge`,
`enablevideo`) before using it.

## When the player is used

`videotimeplugin_live_embed_player()` returns the player when the plugin is enabled,
`block_deft/enableupdating` is set and the activity has a record in `videotimeplugin_live`. That
record exists while the activity's **Live feed** (`livefeed`) is checked; unchecking it deletes the
record.

## Data

| Table | Content |
| --- | --- |
| `videotimeplugin_live` | One row per live activity with its embed options. |
| `videotimeplugin_live_peer` | One row per connection: activity, Moodle session, user, mute state, `status` (1 = closed), times. |
| `block_deft_room` | The video room, with `component` = `videotimeplugin_live` and `itemid` = Video Time instance id. Its `data` JSON holds `feed`, the peer id that is currently broadcast (0 for none). |

## Web services

All are available through AJAX and in the Moodle app service.

| Function | Type | Purpose |
| --- | --- | --- |
| `videotimeplugin_live_get_room` | read | Room id, server and ICE servers for a module context. |
| `videotimeplugin_live_join_room` | write | Joins the Janus video room as subscriber or publisher. Joining without a feed creates a new peer record and needs `block/deft:sharevideo`. |
| `videotimeplugin_live_publish_feed` | write | Starts or stops broadcasting a peer's feed. Publishing needs `videotimeplugin/live:sharevideo`; replacing another feed needs `videotimeplugin/live:moderate`. |
| `videotimeplugin_live_get_feed` | read | The feed currently broadcast. |
| `videotimeplugin_live_renew_token` | read | A new token for the Deft message service. |

When the feed changes, `publish_feed` dispatches a message through `\videotimeplugin_live\socket`;
the clients then call `get_feed` and switch their subscription.

## Events

`publish_feed` triggers `\videotimeplugin_live\event\video_started` when a feed is published and
`\videotimeplugin_live\event\video_ended` when it is stopped. Their names come from the Deft
response block (*Video started*, *Video ended*).

## JavaScript

`videotimeplugin_live/videotime` extends `mod_videotime/videotime`. On the activity page it fetches
the room, subscribes to the socket and shows the current feed. The controls of the **Shared video**
block (template `videotimeplugin_live/controls`) are added as a fake block in
`videotimeplugin_live_setup_page()`.
