# Video Time Chat tab — Administrator guide

The Chat tab gives Video Time activities a chat next to the video. It is not enabled when it is
installed.

## Requirements

- The Deft response block (`block_deft`), newer than version 2022111400. It delivers new messages
  to the open pages.
- Moodle comments: **Enable comments** (*Site administration ▸ Advanced features*) must be on,
  because messages are stored as comments.

## Enabling the tab

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time tab plugins ▸ Manage
Video Time tab plugins*: use the eye icon in **Hide/Show**. Without a suitable Deft response block,
the tab cannot be enabled: **Hide/Show** is empty and **Status** shows "This plugin requires
installation of Deft response block to enable." The arrows in **Order** set the position of the tab
among the other tabs.

While the tab is disabled, its settings page is not listed in the menu; the **Settings** link on the
management page still opens it.

## Settings

*Site administration ▸ Plugins ▸ Activity modules ▸ Video Time ▸ Video Time tab plugins ▸ Video
Time Chat tab*

**Default** (unchecked by default): whether **Show tab** is checked for the Chat tab in new
activities.

## Permissions

The chat uses the comment capabilities in the activity:

- `moodle/comment:post` to write messages, and to delete one's own;
- `moodle/comment:delete` to delete messages of others.

Everyone who can open the tab can read the messages.

## Privacy

Messages are stored and exported as Moodle comments of the activity. The plugin's privacy metadata
also names the LTI connection to deftly.us that the Deft response block uses to signal updates;
according to it, no user information is sent.
