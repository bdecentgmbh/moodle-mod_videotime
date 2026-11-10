# Video Time

Video Time is an activity for Moodle that embeds videos (Vimeo, uploaded media files and other sources) in a course,
on its own page or right on the course page. Tabs around the video show information, chapters, a transcript, related
activities and more, and the activity can be completed by viewing.

Video Time is free. Two paid editions add features through add-ons that are installed inside it:

| Edition | Contains | Get it from |
| --- | --- | --- |
| Video Time | `mod_videotime` with its players and tabs (this repository) | [Moodle plugins directory](https://moodle.org/plugins/mod_videotime), or the releases of this repository |
| Video Time Pro | Video Time with `videotimeplugin_pro`: watch-time tracking and completion, resume, label mode, interactions, reports and more | [bdecent.de](https://bdecent.de/product/video-time-pro/) |
| Video Time Premium | Video Time Pro with `videotimeplugin_repository`: choose videos from your Vimeo account in Moodle, with their metadata | [bdecent.de](https://bdecent.de/product/video-time-premium/) |

More about Video Time: <https://bdecent.de/video-time/>. Documentation:
[docs](docs/) in this repository and the
[Video Time help center](https://intercom.help/bdecent/en/collections/12188418-video-time).

## Requirements

Moodle 4.4 to 5.3 (`$plugin->requires` and `$plugin->supported` in [version.php](version.php)).

## Installation

- **ZIP:** download Video Time from the [Moodle plugins directory](https://moodle.org/plugins/mod_videotime) or from
  the releases of this repository, and upload it under *Site administration > Plugins > Install plugins*.
- **On the server:** unzip it into `mod/videotime` (`public/mod/videotime` from Moodle 5.1), or clone this repository
  there, then go to *Site administration > Notifications*.
- **Pro and Premium:** customers download a bundle from their account on bdecent.de: Video Time with the add-ons
  already inside (`plugin/pro`, `plugin/repository`). Install it like Video Time, in its place. Video Time, Pro and
  Premium always have the same release; update them together.

## Components

Besides the activity, this repository contains its players and tabs. `plugin/pro` and `plugin/repository` come from
two private repositories; [.gitignore](.gitignore) keeps them out of this one.

| Component | Folder | Purpose |
| --- | --- | --- |
| `mod_videotime` | `/` | The activity |
| `videotimeplugin_vimeo` | `plugin/vimeo` | Vimeo player |
| `videotimeplugin_videojs` | `plugin/videojs` | Video.js player for media files and other sources |
| `videotimeplugin_live` | `plugin/live` | Live player, with [Deft](https://moodle.org/plugins/block_deft) |
| `videotimetab_watch` | `tab/watch` | Watch tab |
| `videotimetab_information` | `tab/information` | Information tab |
| `videotimetab_chapter` | `tab/chapter` | Chapters tab |
| `videotimetab_texttrack` | `tab/texttrack` | Transcript tab |
| `videotimetab_related` | `tab/related` | Related activities tab |
| `videotimetab_interaction` | `tab/interaction` | Interactions tab |
| `videotimetab_block` | `tab/block` | Blocks tab |
| `videotimetab_chat` | `tab/chat` | Chat tab |
| `videotimeplugin_pro` | `plugin/pro` | Video Time Pro ([moodle-mod_videotime_pro](https://github.com/bdecentgmbh/moodle-mod_videotime_pro), private) |
| `videotimeplugin_repository` | `plugin/repository` | Video Time Premium ([moodle-mod_videotime_repository](https://github.com/bdecentgmbh/moodle-mod_videotime_repository), private) |

## Development

- Work happens on `dev`: one branch per ClickUp task (e.g. `VID-1234`), made from `dev` and merged back with a pull
  request. Pro and Premium use the same branch names, so that paired changes are found together.
- `master` holds what is released. It is only updated by merging `dev` for a release.
- [Moodle Plugin CI](.github/workflows/moodle-ci.yml) runs on every push and pull request.
- [CHANGES.md](CHANGES.md) lists the changes of each release: add a line with every change that users notice.

## Releases

A release is the same tag, e.g. `v1.13-r1`, in this repository, in moodle-mod_videotime_pro and in
moodle-mod_videotime_repository. `<release>` is `$plugin->release` in `version.php`, the same in all three.

| What | Published to | By |
| --- | --- | --- |
| Video Time | The GitHub release of this repository, the Moodle plugins directory, the Video Time product on bdecent.de | The [Release workflow](.github/workflows/release.yml), when the GitHub release is published |
| Video Time Pro | The stable branches and releases of [video-time-pro](https://github.com/bdecentgmbh/video-time-pro), the Video Time Pro product on bdecent.de | *Publish Video Time Pro* in video-time-pro, started by hand |
| Video Time Premium | The stable branches and releases of [video-time-premium](https://github.com/bdecentgmbh/video-time-premium), the Video Time Premium product on bdecent.de | *Publish Video Time Premium* in video-time-premium, started by hand |

Pro and Premium are never published to the Moodle plugins directory. moodle-mod_videotime_pro and
moodle-mod_videotime_repository only run CI; their code reaches customers through the two bundle repositories.

### Steps

1. In all three repositories: set `$plugin->release` (e.g. `1.13`) and `$plugin->version` in `version.php`, and add a
   section `## <release> (<version>)` with the changes at the top of `CHANGES.md`, e.g. `## 1.13 (2026090102)`. Then
   merge `dev` into `master` with a pull request.
2. Tag the merge commits of moodle-mod_videotime_pro and moodle-mod_videotime_repository, e.g.
   `git tag v1.13-r1 origin/master && git push origin v1.13-r1`. Nothing runs there.
3. Here: *Releases > Draft a new release*, tag `v1.13-r1` on `master`, a title such as *Video Time 1.13*; publish it.
   The release workflow checks that the tag matches `version.php` and is on `master`, that CHANGES.md has the section,
   and waits until Moodle Plugin CI has passed on the tagged commit. Then it attaches `mod_videotime_1.13-r1.zip`,
   publishes it to the Moodle plugins directory, gives the ZIP to the Video Time product on bdecent.de and
   staging.bdecent.de and creates a draft release note there. A release published without text gets the CHANGES.md
   section as its notes.
4. In video-time-pro: *Actions > Publish Video Time Pro > Run workflow* with `source_ref` `v1.13-r1`. It commits the
   bundle to every stable branch (`MOODLE_404_STABLE` to `MOODLE_503_STABLE`), tags and releases it with the ZIP, and
   gives the ZIP to the Video Time Pro product on bdecent.de. From a terminal:
   `gh workflow run publish.yml -R bdecentgmbh/video-time-pro -f source_ref=v1.13-r1`
5. The same in video-time-premium (*Publish Video Time Premium*).
6. Edit and publish the draft release note on bdecent.de.

The bundle workflows stop before they push anything if the Video Time release of the tag has no ZIP yet (its release
workflow has not passed), if an add-on is of another release or needs a newer Video Time, or if the tag exists in the
bundle repository already. See the READMEs of video-time-pro and video-time-premium.

**Further releases of the same version** (R2, R3 …): merge the fixes into `master` as above, raise `$plugin->version`,
add a new section at the top of CHANGES.md with the same release and the new version (e.g. `## 1.13 (2026090103)`), and
tag `v1.13-r2`.

**Pre-releases:** tick *Set as a pre-release* and use a tag `v<release>-<suffix>` from any branch, e.g. `v1.13-rc2`.
The ZIP goes to staging.bdecent.de only, not to the plugins directory. A bundle of a pre-release is a pre-release too
and also goes to staging only. Unticking the box later runs the release steps.

**Running a step again:** *Actions > Release > Run workflow* with the tag, optionally as a dry run. Products on
bdecent.de that already have the tag are skipped.

**Totara** builds (tags such as `v1.8.2-t18` or `v1.5t`) are not published by these workflows; the release workflow
stops on them. Publish them by hand in the product's GitHub tab on bdecent.de.

### Secrets

| Repository | Secret | Purpose |
| --- | --- | --- |
| moodle-mod_videotime | `MOODLE_MARKETPLACE_TOKEN` | API token of a maintainer of Video Time in the Moodle plugins directory |
| moodle-mod_videotime, video-time-pro, video-time-premium | `BDECENT_RELEASE_SECRET`, `BDECENT_STAGING_AUTH` | The release webhook of bdecent.de and the basic auth of staging.bdecent.de |
| video-time-pro, video-time-premium | `VIDEOTIME_READ_TOKEN` | Reads the private add-on repositories |

## License

2021 bdecent gmbh <https://bdecent.de>

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see
<https://www.gnu.org/licenses/>.
