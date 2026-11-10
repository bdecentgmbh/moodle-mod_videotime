# Video Time Interactions tab — Teacher guide

The Interactions tab makes a Video Time activity interactive. At times you choose in the video, it
shows information, asks viewers to confirm that they are still watching, or asks questions from the
question bank. It can also show prompts at random times. The tab requires Video Time Pro.

## Switching it on

In the activity settings, under **Tabs**:

1. Check **Enable tabs**.
2. Check **Show tab** next to **Video Time Interactions tab**.
3. Set **Interval** for random prompts, or uncheck its **Enable** checkbox (see below).
4. Save.

The fields only appear when your administrator has enabled the tab for the site.

Then open the activity and select the **Interactions** tab. Users who may manage the activity see
**Edit prompts** there, and **Edit questions** if they also have the capability to edit questions,
which by default only managers have. Both open a dialog.

## Times in the editors

Each row of the editors has a start and an end time in seconds; the interaction is active between
them. A new row starts at the current position of the video. The start must be before the end and
not after the end of the video. Rows without an end time are not saved.

## Prompts

**Edit prompts** lists the **Cues** of the activity, one row per cue (**Cue 1**, **Cue 2**, …) with
the times, an **Action**, a text editor for the content and **Delete**.

- **Information**: the content is shown in the Interactions tab while the cue is active.
- **Continue**: the content is shown below the tabs with a countdown ("Time remaining 10 s") and a
  **Continue** button. If the viewer does not click it in time, the video pauses.

The length of the countdown is a site setting (10 seconds by default).

## Questions

Questions must be in the question bank before you can use them. In **Edit questions**:

1. Under **Category**, choose the question category. **Include questions from subcategories too** is
   checked by default.
2. Under **Questions**, add one row per question (**Question 1**, …): the times, the behaviour, and
   the question, which you find by typing its name.

| Behaviour | Effect |
| --- | --- |
| **No pause** | The video plays on while the question is shown. |
| **Pause if incomplete** | The video pauses if the viewer has not answered the question yet. |
| **Pause video** | The video always pauses when the question appears. |

When a question becomes active, the Interactions tab opens and shows it. When its end time is
reached, the question disappears and the viewer's response so far is saved. Viewers see the
feedback and whether their answer is correct, but no marks. Each viewer has one attempt per
activity. If the activity has questions, viewers see a **Reset** button that erases their answers
to all questions.

## Random prompts

With **Interval** enabled, the prompt "Click to continue watching video" appears at random moments
while the video plays, on average after the time you set. It works like a **Continue** cue. After
each prompt the viewer confirms, the average interval grows by a factor that your administrator
sets; a missed prompt resets it.

In new activities, **Interval** is enabled with the site default (1 minute unless your
administrator changed it).

## What participants see

Prompts and questions appear while they watch. When a **Continue** prompt, a random prompt or a
question appears, the video leaves fullscreen. The logs record the events *Interaction viewed*,
*Answer submitted* and *Timer expired*.
