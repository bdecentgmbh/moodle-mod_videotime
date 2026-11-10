# Video Time Transcript tab — Administrator guide

The Transcript tab shows the text tracks of a video as a transcript. It requires Video Time Premium
(`videotimeplugin_repository`, which itself needs Video Time Pro) and is not enabled when it is
installed.

## Enabling the tab

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time tab plugins ▸ Manage
Video Time tab plugins*: use the eye icon in **Hide/Show**. Without Video Time Premium, the tab
cannot be enabled: **Hide/Show** is empty and **Status** shows "This plugin requires installation
of Video Time Premium to enable." The arrows in **Order** set the position of the tab among the
other tabs.

While the tab is disabled, its settings page is not listed in the menu; the **Settings** link on the
management page still opens it.

## Settings

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time tab plugins ▸ Video
Time Transcript tab*

**Default** (checked by default): whether **Show tab** is checked for the Transcript tab in new
activities.

The text tracks of Vimeo videos are fetched through the Vimeo API connection of Video Time Premium.
If that connection is not configured, site administrators see its error message when the tab
reloads the tracks.

## Permissions

- `moodle/course:managefiles`: see text tracks that are not visible, and the **Edit** button for
  tracks uploaded in the activity.
- `moodle/course:manageactivities`: save changes made with **Edit**.

## Global search

Video Time has the search area **VideoTime - text track information** for transcript lines. It
indexes them only while the Transcript tab is enabled and Video Time Premium is available.
