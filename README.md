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
- [Moodle Plugin CI](.github/workflows/moodle-ci.yml) runs for pull requests and for pushes to `master`.
- [CHANGES.md](CHANGES.md) lists the changes of each release: add a line with every change that users notice.

## Releases

How Video Time, Pro and Premium are released: [.github/RELEASING.md](.github/RELEASING.md).

## License

2021 bdecent gmbh <https://bdecent.de>

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see
<https://www.gnu.org/licenses/>.
