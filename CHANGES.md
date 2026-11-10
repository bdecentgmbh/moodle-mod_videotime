# Changes

Changes of Video Time (`mod_videotime` and the players and tabs in this repository). The changes of Video Time Pro and
Premium are in the CHANGES.md of their repositories; the bundles on bdecent.de list all three.

## 1.13 (2026090102)

- **Moodle 4.4 to 5.3.** Video Time 1.13 supports Moodle 5.3.
- **Interactions tab.** A new tab for the interactions of Video Time Pro: continue prompts and questions from the
  question bank at set times of the video. The tab comes to the front when a prompt appears. A question can pause the
  video always, only while it is incomplete, or not at all, and attempts can be reset. Random prompts can be spaced
  further apart. The tab needs Video Time Pro.
- **Media Time text tracks in the player.** The new option **Display Media Time tracks** shows the text tracks of a
  Media Time resource in the Video.js player, without importing them first. Like the other options, it can be set as
  a site default, as an advanced setting or forced.
- **Forced settings in the advanced settings.** Settings that an administrator has forced now show up in the advanced
  settings of the activity.
- **Ignore prevent fast forwarding.** The new capability `mod/videotime:ignorepreventfastforwarding` lets roles that
  are given it skip ahead in videos that prevent fast forwarding. No role has it by default.
- **Notes on small screens.** Without tabs, the notes stay visible below the video on narrow screens.
- **Activity overview.** The overview of Video Time activities in a course checks capabilities in the activity and no
  longer shows the number of views.
- **Duplicating.** Duplicating a Video Time with interactions no longer ends in an error.
- **Embedded player.** The player page used for embedding knows its course module.
- **Premium.** The former Business edition is called Video Time Premium everywhere, with links to its product page.
- **New icon.** The monochrome activity icon was updated.
- **Security and code quality.** Fixes from a code review.
- **For developers.** The players and tabs declare their dependency on `mod_videotime` with its full component name.

## Earlier releases

See the [releases on GitHub](https://github.com/bdecentgmbh/moodle-mod_videotime/releases).
