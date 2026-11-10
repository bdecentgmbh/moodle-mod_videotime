# Video Time — Administrator guide

This guide covers installing Video Time, its site settings, the players and tabs it consists of,
its capabilities and how it handles personal data. For setting up an activity see the
[teacher guide](teacher-guide.md); for an overview of the editions see the [readme](README.md).

## Requirements

- Moodle 4.4 to 5.3.
- The *Video Time VideoJS Player* and *Video Time Live Player* need Moodle's Video.js media player
  (`media_videojs`), part of standard Moodle.
- The *Video Time Live Player* and the **Chat** tab need the Deft response block (`block_deft`).
- Video Time Pro and Video Time Premium need Video Time of the same release; update them together.

## Installation

Install Video Time like any other activity module: upload the ZIP under *Site administration ▸
Plugins ▸ Install plugins*, or put the code into `mod/videotime` and visit *Site administration ▸
Notifications*. The players and tabs in `mod/videotime/plugin` and `mod/videotime/tab` are
installed with it.

On a new installation, the *Video Time Vimeo Player* and *Video Time VideoJS Player* and the
**Watch** and **Information** tabs are enabled. Enable the other players and tabs on the manage
pages described below.

Video Time Pro goes into `mod/videotime/plugin/pro`, Video Time Premium (*Video Time Repository*)
into `mod/videotime/plugin/repository`. Premium needs Pro.

## Settings

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ General settings*

These are the defaults for new activities and for the activity form. Without Pro, the page also
shows information about Video Time Pro.

| Setting | Default | Effect |
| --- | --- | --- |
| **Default Show description above the player** | Yes | Default of the activity setting **Show description above the player**. |
| **Default Enable tabs** | No | Default of the activity setting **Enable tabs**. |
| **Default tab size** | Medium panel width | Initial width of the tab content next to the video: **Small panel width** (a quarter), **Medium panel width** (half) or **Large panel width** (three quarters). Participants can drag the divider to change it. |
| **Default text track visibility** | Yes | Whether a new text track is visible to students in the tabs and in the VideoJS player. Text tracks uploaded to the activity are not shown in the Vimeo player. |
| **Default text track type** | Chapters | The type preselected for a new text track: **Chapters**, **Captions** or **Subtitles**. |
| **Mobile iframe player** | No | Shows the activity in the Moodle app through an embedded web page. It is needed for tabs and for videos that are not from Vimeo in the app. The help text calls it experimental. |

The Video Time category in *Activity modules* is hidden while the Video Time module is disabled.

## Players and tabs

Players are subplugins of the type *Video Time Plugin*, tabs of the type *Video Time Tab*. Each
type has a manage page:

- *Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time Plugins ▸ Manage
  Video Time extensions*
- *Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time tab plugins ▸
  Manage Video Time tab plugins*

The table lists every installed plugin of the type with its **Version** and these columns:

- **Hide/Show**: enables or disables the plugin for the whole site.
- **Order**: moves the plugin up or down. The order of the tab plugins is the order of the tabs in
  every activity.
- **Settings**: opens the plugin's settings, if it has any. They are also listed under *Video Time
  Plugins* and *Video Time tab plugins* in the Video Time category.
- **Status**: what the plugin still needs, for example *This plugin requires installation of Video
  Time Pro to enable.* Such a plugin cannot be enabled until the requirement is met.
- **Uninstall**: uninstalls the plugin.

Video Time Pro and Video Time Premium are listed on the Video Time Plugins page too. Disabling Pro
there switches off all Pro features, including those of Premium, without uninstalling them. To
test the free features on a site with Pro, you can also add `$CFG->disable_videotime_pro = true;`
to `config.php`; `$CFG->disable_videotime_repository = true;` does the same for Premium.

## Capabilities

| Capability | Default roles | Allows |
| --- | --- | --- |
| `mod/videotime:view` | Authenticated user, Guest | Opening a Video Time activity and watching its video, also in the Moodle app |
| `mod/videotime:addinstance` | Editing teacher, Manager | Adding Video Time activities. Carries the XSS risk: the description and the **Notes** of an activity are shown without HTML cleaning. |
| `mod/videotime:view_report` | Teacher, Editing teacher | With Pro: the activity's **Report**, the **View report** links in the list of the course's Video Time activities and the *Video Time* entry in the course reports |
| `mod/videotime:ignorepreventfastforwarding` | None | With Pro: seeking beyond the watched part of a video although **Prevent fast forwarding** is on |

Managers do not have `mod/videotime:view_report` by default. The **Embed options** page of an
activity is controlled by the core capability `moodle/course:manageactivities`.

Site administrators also see a **Delete session data** button for each user in the report of an
activity (Pro). It deletes the user's viewing data for that activity in a background (ad hoc) task.

## Scheduled tasks

Video Time itself has no scheduled tasks. Video Time Premium has tasks for reading the videos of
the Vimeo account; see its guide.

## Moodle app

Video Time supports the Moodle app. Without **Mobile iframe player**, the app plays Vimeo videos
with its own player. With it, the app shows the activity's player page in an embedded web page;
Video Time signs the user in to that page with the user's token for the Moodle app web service.

## Global search

Video Time adds two search areas, enabled under *Site administration ▸ Plugins ▸ Search ▸ Search
areas*:

- **VideoTime - activity information**: the activities, including their files.
- **VideoTime - text track information**: the text of the text tracks. It returns results only
  with Video Time Premium and an enabled **Transcript** tab.

## Dash

When the Dash block (`block_dash`) is installed, Video Time provides the Dash data source **Video
Time stats**: one row per Video Time activity with its name, ID number, link, notes and
description. With Pro it adds viewing figures (**Total unique visitors**, **Total views**,
**Average view time**, **Percentage of video finished**, **First session**, **Last session**),
with Premium the video's creation date, its preview image and the completion settings.

## Privacy

Video Time itself stores no personal data, and its privacy provider says so. The viewing sessions
and interaction attempts are stored by Video Time Pro, which declares and exports them in its own
privacy provider.

## Pro and Premium settings

The settings of Video Time Pro and Video Time Premium are under *Site administration ▸ Plugins ▸
Activity modules ▸ Video Time ▸ Video Time Plugins*; with both installed, the Video Time category
also has the pages **Authenticate with Vimeo** and **Overview and setup**. They are described in
the Video Time Pro and Video Time Premium guides.
