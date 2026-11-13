# Video Time Interactions tab — Administrator guide

The Interactions tab lets teachers add information, continue prompts and question bank questions at
chosen times of a video, and random prompts that check attention. It requires Video Time Pro
(`videotimeplugin_pro`), which provides its editors and its JavaScript. It is not enabled when it is
installed.

## Enabling the tab

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time tab plugins ▸ Manage
Video Time tab plugins*: use the eye icon in **Hide/Show**. Without Video Time Pro, the tab cannot
be enabled: **Hide/Show** is empty and **Status** shows "This plugin requires installation of Video
Time Pro to enable." The arrows in **Order** set the position of the tab among the other tabs.

While the tab is disabled, its settings page is not listed in the menu; the **Settings** link on the
management page still opens it.

## Settings

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time tab plugins ▸ Video
Time Interactions tab*

| Setting | Default | Effect |
| --- | --- | --- |
| Default | Checked | Whether **Show tab** is checked for the Interactions tab in new activities. |
| Interval | 1 minute | The default average time between random prompts in new activities. Teachers change it per activity. It must be shorter than one day. |
| Count down | 10 | Seconds a viewer has to click **Continue** before the video pauses. Applies to continue cues and random prompts. |
| Ratio | 100% | After each prompt the viewer answers, the average interval between random prompts is multiplied by this value (100% to 400%). A missed prompt resets it. |
| How questions behave | Adaptive mode | The question behaviour of the questions shown in videos. |

## Capabilities

| Capability | Default | Allows |
| --- | --- | --- |
| `videotimetab/interaction:editquestions` | Manager | Opening **Edit questions** and saving questions. The button is only shown to users who also have `moodle/course:manageactivities`. |
| `videotimetab/interaction:resetattempt` | Student, Non-editing teacher, Teacher | The **Reset** button that erases one's own answers. |

**Edit prompts** requires `moodle/course:manageactivities`. In **Edit questions**, users can pick
questions from the categories in which they have `moodle/question:useall`.

Because only managers can edit questions by default, give `videotimetab/interaction:editquestions`
to teachers if they should add questions themselves.

The capabilities `videotimetab/interaction:interact` and `videotimetab/interaction:edit` are
defined, but this version does not check them.

## Data

Each participant's answers are one question attempt per activity, stored by Video Time Pro. They
are erased by **Reset**.
