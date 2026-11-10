# Video Time — Overview

Video Time (`mod_videotime`) is an activity module for showing a video in a Moodle course. A
teacher adds a Video Time activity and enters the address of a video. Depending on the installed
players, the teacher can instead upload a media file or offer a live feed. Tabs next to the video
can show more content, such as information, chapters or a transcript, and text tracks add
captions, subtitles and chapters.

The activity is free. Its paid extensions record what each participant watches, add the features
built on that, and connect Moodle to a Vimeo account.

## Editions

| Edition | What is installed | What it adds |
| --- | --- | --- |
| **Video Time** | `mod_videotime` with the players and tabs in this repository | Videos from Vimeo, YouTube and other sources, uploaded media files, tabs, text tracks, completion when the activity is viewed |
| **Video Time Pro** | Video Time and `videotimeplugin_pro` | Completion on view time, watch percentage or video finish; a grade equal to the view percentage; reports of the viewing sessions; label mode (the video on the course page); resuming playback; a button to the next activity; preventing fast forwarding; the Chapters and Interactions tabs |
| **Video Time Premium** | Video Time Pro and `videotimeplugin_repository`, listed in Moodle as *Video Time Repository* | A connection to a Vimeo account: teachers choose a video from the account and pull its metadata into the activity; preview image mode; the Transcript tab |

Pro and Premium are separate packages, installed into `mod/videotime/plugin/pro` and
`mod/videotime/plugin/repository`. Video Time offers the Pro features only while
`videotimeplugin_pro` is installed and enabled, and the Premium features only when Pro is enabled
as well.

## Components

| Component | Directory | Purpose |
| --- | --- | --- |
| `mod_videotime` | `mod/videotime` | The activity: its settings, display modes, tabs, text tracks and web services |
| `videotimeplugin_vimeo` | `mod/videotime/plugin/vimeo` | *Video Time Vimeo Player*: plays Vimeo videos |
| `videotimeplugin_videojs` | `mod/videotime/plugin/videojs` | *Video Time VideoJS Player*: plays other videos, for example from YouTube or a file address, and uploaded media files |
| `videotimeplugin_live` | `mod/videotime/plugin/live` | *Video Time Live Player*: a live feed supplied by a teacher, using the Deft response block |
| `videotimetab_watch` | `mod/videotime/tab/watch` | **Watch** tab: the video at full width |
| `videotimetab_information` | `mod/videotime/tab/information` | **Information** tab: a text written by the teacher |
| `videotimetab_block` | `mod/videotime/tab/block` | **Blocks** tab: blocks added on the activity page |
| `videotimetab_chapter` | `mod/videotime/tab/chapter` | **Chapters** tab: the chapters of the video (needs Pro) |
| `videotimetab_chat` | `mod/videotime/tab/chat` | **Chat** tab (needs the Deft response block) |
| `videotimetab_interaction` | `mod/videotime/tab/interaction` | **Interactions** tab: interactions, such as questions, at set times in the video (needs Pro) |
| `videotimetab_related` | `mod/videotime/tab/related` | **Related activities** tab: the other activities of the same course section |
| `videotimetab_texttrack` | `mod/videotime/tab/texttrack` | **Transcript** tab: the text of the video's text tracks (needs Premium) |
| `videotimeplugin_pro` | `mod/videotime/plugin/pro` | *Video Time Pro* (separate package) |
| `videotimeplugin_repository` | `mod/videotime/plugin/repository` | *Video Time Repository*, the Premium extension (separate package) |

The players are subplugins of the type *Video Time Plugin*, the tabs of the type *Video Time
Tab*. Administrators enable, disable and order them; see the administrator guide.

## Choose your guide

| You are… | You want to… | Guide |
| --- | --- | --- |
| Administrator | Install Video Time, set the site defaults, manage players and tabs, set permissions | [Administrator guide](admin-guide.md) |
| Teacher | Add a Video Time activity and set it up | [Teacher guide](teacher-guide.md) |
| Student | Watch a video and use the tabs | [Student guide](student-guide.md) |
| Developer | Write a player or tab plugin, use the web services, run the tests | [Developer guide](developer-guide.md) |

These guides cover the activity itself. Each player and each tab has its own guides, for example
the *Video Time Vimeo Player* guides or the *Video Time Information tab* guides. Video Time Pro and
Video Time Premium (*Video Time Repository*) are documented in their own guides as well. In the
Documentation Center they appear together with these guides on every Video Time page.

## Requirements and support

- Moodle 4.4 to 5.3.
- The VideoJS and Live players need Moodle's Video.js media player (`media_videojs`), which is part
  of standard Moodle.

Bug reports and feature requests: [moodle-mod_videotime on GitHub](https://github.com/bdecentgmbh/moodle-mod_videotime).
