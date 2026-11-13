# Video Time Interactions tab — Developer guide

`videotimetab_interaction` holds the settings, the cues and the question links of an activity. The
editors, the delivery to the player and the question attempts are implemented in Video Time Pro
(`videotimeplugin_pro`); `tab::added_dependencies()` keeps the tab disabled without it.

## Data

| Table | Content |
| --- | --- |
| `videotimetab_interaction` | One row per activity with the tab: `spacing` (random prompt interval in seconds, 0 or empty for none) and `timemodified` (changes when cues are saved). |
| `videotimetab_interaction_cue` | One row per cue: `action`, `starttime`, `endtime` (seconds) and `data`. |
| `videotimetab_interaction_question` | Links a `questions` cue (`cueid`) to a question (`questionid`). |

Values of `action`:

| Action | `data` |
| --- | --- |
| `information`, `continue` | JSON with `text` and `format`. Embedded files are in the file area `videotimetab_interaction/content`, item id = cue id. |
| `questions` | JSON with `mandate`: `nopause`, `pausetodo` or `pause`. |
| `random` | The prompt text. One row per activity, created while the activity's **Interval** is set; it has no times. |

## Question bank

The questions are referenced in `question_references` with `component` = `mod_videotime`,
`questionarea` = `slot`, `usingcontextid` = the module context and `itemid` =
`videotimetab_interaction_question.id`; Pro rewrites them whenever the questions are saved. Pro
keeps one question usage per user and activity (component `mod_videotime`) in
`videotimeplugin_pro_attempt`, with the preferred behaviour from
`videotimetab_interaction/preferredbehaviour`. The usage gets one slot per question cue, in the
order of their start times, when it is created.

Backup and restore of the cues and their files is done by this subplugin; the question links are
handled by the Video Time backup steps.

## Delivery to the player

When the activity has cues, Pro writes a WebVTT file with one cue per row (except `random`), whose
text is the cue id. It is served as `videotimeplugin_pro/metadata/0/<timemodified>/metadata.vtt` in
the module context. The VideoJS player loads it as a `metadata` text track. For Vimeo, Pro's web
service `videotimeplugin_pro_get_metadata` returns the cues, which are added as cue points.

The content of an active cue is loaded with the fragment `videotimeplugin_pro` / `interaction`; the
same fragment receives question responses. The JavaScript modules are in Pro:
`videotimeplugin_pro/interactions` and `videotimeplugin_pro/local/interaction/*`. The tab template
starts `videotimeplugin_pro/local/interaction/random` when the activity has an interval.

## Events

All have `objecttable` = `videotimetab_interaction_cue`, `objectid` = cue id and, except
`timer_ended`, `other['starttime']`.

| Event | When |
| --- | --- |
| `\videotimetab_interaction\event\interaction_viewed` | The content of a cue is loaded. |
| `\videotimetab_interaction\event\answer_submitted` | A question response is processed. |
| `\videotimetab_interaction\event\timer_ended` | A continue or random prompt was not confirmed in time. |
