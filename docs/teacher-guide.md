# Video Time — Teacher guide

A Video Time activity shows one video in your course. You can add tabs next to the video, for
example with information, chapters or a transcript, and text tracks for captions, subtitles and
chapters. Which kinds of videos you can use depends on the players your site has enabled.

Some settings and features need Video Time Pro or Video Time Premium. They appear only when your
site has them; this guide marks them, and the Video Time Pro and Video Time Premium guides describe
them in detail.

## Adding a Video Time activity

1. Turn editing on in your course.
2. Choose *Add an activity or resource* and pick **Video Time**.
3. Enter the **Video URL**: the full address of a Vimeo video, a YouTube video, or a video or
   audio file on the web. Copy it from your browser's address bar.
4. Enter an **Activity name**.
5. Adjust the other settings described below, then save.

You can also drag a video or audio file onto the course page and choose **Create Video Time
activity**. This works for the file types a player offers to handle; the VideoJS player, for
example, handles mp4, webm and other common formats.

## General

| Setting | Effect |
| --- | --- |
| **Video URL** | The video to show. It is required unless the video comes from another source a player adds to the form, such as an uploaded **Media file** (VideoJS player) or a **Live feed** (Live player). |
| **Activity name** | The name shown in the course. |
| **Description** | The activity description. |
| **Display description on course page** | Shows the description on the course page as well. |
| **Show description above the player** | Shows the description at the top of the activity page. If it is off, the description is not shown on the activity page. The default comes from the site settings. |
| **Notes** | Text shown below the video. |

With Video Time Premium, **Pull Metadata from Vimeo** fills in the activity from the Vimeo
video, and **Choose Video** lets you pick a video from the connected Vimeo account.

With Video Time Pro, this section also has **Mode** (see *Display on the course page*),
**Resume Playback**, **Enable Next Activity Button**, **Next Activity**, **Automatically Go To Next
Activity**, **Tracking interval** and **Prevent fast forwarding**.

## Tabs

Check **Enable tabs** to show the video with tabs. Below it, each tab that is enabled on your site
has a checkbox **Show tab**, labelled with the tab's name (for example *Video Time Information
tab*), and the tab's own settings. A tab's checkbox can only be changed while **Enable tabs** is
checked. The tabs appear in the order your administrator has set.

| Tab | Shows | Guide |
| --- | --- | --- |
| **Watch** | The video at full width | Video Time Watch tab |
| **Information** | A text you enter in the activity settings | Video Time Information tab |
| **Blocks** | Blocks you add on the activity page | Video Time Block tab |
| **Chapters** | The chapters of the video (needs Pro) | Video Time Chapter tab |
| **Chat** | A chat (needs the Deft response block) | Video Time Chat tab |
| **Interactions** | Interactions, such as questions, at set times in the video (needs Pro) | Video Time Interactions tab |
| **Related activities** | The other activities of the same course section, with your instructions | Video Time Related activities tab |
| **Transcript** | The text of the video's text tracks (needs Premium) | Video Time Transcript tab |

On wide screens, the video stays next to the content of the active tab; on narrow screens it is
shown above the tabs.

## Text tracks

Text tracks are WebVTT files (`.vtt`) with captions, subtitles or chapters. For each track:

- **Text track 1**, **Text track 2**, …: the label of the track.
- **Track type**: **Chapters**, **Captions** or **Subtitles**.
- **Language**: the language of the track. New tracks get your current language.
- **Visible**: whether students see the track.
- **Default**: makes the track the default one.
- The file: one `.vtt` file of up to 20 MB.
- **Delete** removes the track.

Click **Add text track** for another track. The default type and visibility of new tracks come
from the site settings. Visible tracks are offered in the VideoJS player and in tabs such as
**Transcript**; the Vimeo player does not show tracks uploaded here.

## Other settings

The form continues with Moodle's standard sections, such as *Common module settings*, *Restrict
access* and *Completion conditions*. With Video Time Pro it also has an **Availability** section
(**Allow viewing from**, **Allow viewing until**, **Prevent preview tracking**) and a **Grade**
section. The viewing dates appear in the calendar and on the timeline as *… begins* and *… ends*.
Players may add more settings.

## Display on the course page

Without Pro, a Video Time activity is a link on the course page, like most activities. With Pro,
**Mode** offers:

- **Normal mode**: the standard activity link, nothing more on the course page.
- **Label mode**: the video is embedded in the course page, like a label. The activity has no link
  of its own.
- **Preview image mode** (Premium): the video's thumbnail on the course page, linking to the
  activity.

## Embed options

The options of the player, for example autoplay or the player size, are on a separate page. Open
the activity and choose **Embed options** in the activity's navigation (if it does not fit, it is
under **More**). You need the permission to manage activities in the course.

The page shows the options of the player that plays the activity's video, so it differs between
Vimeo videos and other videos. The Video Time Vimeo Player and Video Time VideoJS Player guides
describe the options. An option your administrator has fixed for the whole site is shown as
*… is globally forced to: …* and cannot be changed.

## Completion

Without Pro, you can use Moodle's standard completion: students mark the activity as done, or it
is complete when they view it.

With Pro and automatic completion, there are more conditions:

| Condition | Students see | Complete when |
| --- | --- | --- |
| **Completion on view time** | *View for time …* | The student has watched for at least the time you set |
| **Completion on watch percentage** | *Finish watching … percent* | The student has reached the percentage you set |
| **Completion on video finish** | *Finish video* | The student has finished the video |

A checked condition needs a value: a time greater than zero, or a percentage. **Hide completion
details** shows students only *Complete the activity* instead of the conditions; the activity is
still complete only when all checked conditions are met. If you switch the activity to manual or
no completion, these conditions are turned off.

## Grading

Without Pro, Video Time has no grade. With Pro, the **Grade** section has **Set grade equal to view
percentage.** The student's grade is then the highest view percentage recorded for them, from 0
to 100. **Grade category** and **Grade to pass** can be set once the grade item exists: check the
box, save, and edit the activity again.

## Reports

Reports need Video Time Pro and the capability to view reports, which teachers and editing
teachers have by default.

- **Report** in the activity's navigation shows the viewing data of each participant, with a group
  selector if the activity uses groups, and can be downloaded. The Video Time Pro guide describes
  it.
- *Video Time* in the course's *Reports* lists the course's Video Time activities. In Moodle 4.4
  and 4.5 it shows a **View report** column with the number of views of each activity. In Moodle
  5.0 and later it opens the course's activity overview for Video Time, where **View report** is
  in the **Actions** column.

## Backup, restore and duplicate

Video Time activities can be backed up, restored, duplicated and imported. A backup contains the
activity settings, the files of the description and the notes, the text tracks with their files,
and the settings of the players and tabs. With user data and Pro, it also contains the viewing data
and the answers given in interactions.

When you restore with Pro, a **Next Activity** that was part of the backup is changed to its
restored copy; if it does not exist in the course you restore into, the setting is cleared and no
next activity button is shown. Links to a Video Time activity inside other texts of the course are
not changed on restore and still point to the original activity.

## Features that need Pro or Premium

| Feature | Needs |
| --- | --- |
| Completion on view time, watch percentage or video finish | Pro |
| Grade equal to the view percentage | Pro |
| Reports | Pro |
| Label mode | Pro |
| Resume playback, next activity button, prevent fast forwarding | Pro |
| Viewing dates (**Allow viewing from**, **Allow viewing until**) | Pro |
| Question bank of the activity, used by the **Interactions** tab | Pro |
| **Chapters** and **Interactions** tabs | Pro |
| Choosing a video from Vimeo, pulling metadata from Vimeo | Premium |
| Preview image mode, **Transcript** tab | Premium |
