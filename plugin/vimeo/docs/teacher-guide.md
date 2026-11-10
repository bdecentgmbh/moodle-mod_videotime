# Video Time Vimeo Player — Teacher guide

The Vimeo player plays Vimeo videos in Video Time activities. It is used automatically when the
**Video URL** of an activity is a Vimeo link that contains the video's numeric ID, for example
`https://vimeo.com/123456789` or `https://player.vimeo.com/video/123456789`.

## Adding a Vimeo video

1. Add a Video Time activity to your course.
2. Paste the Vimeo link into **Video URL**.
3. Save.

The player options are not part of the activity settings. A new activity starts with the defaults
of your site, and you change them on the activity's *Embed options* page.

## Embed options

Open the activity and choose **Embed options** in its navigation. The page is available to users
who may manage activities in the course.

| Option | Effect |
| --- | --- |
| **Responsive** | The player adapts to the page or screen size. While it is checked, **Height**, **Width**, **Max height** and **Max width** are disabled. |
| **Height**, **Width** | The exact size of the video. Without a value, the size of the largest available version of the video is used. |
| **Max height**, **Max width** | Like height and width, but the video does not grow beyond its native size. |
| **Autoplay** | Starts the video automatically. Some devices and browsers block this. |
| **Byline**, **Portrait**, **Title** | Show the byline, the portrait and the title on the video. |
| **Controls** | Unchecked, the player hides all its elements, including the play button. Playback then needs **Autoplay** or the keyboard. |
| **Color** | The color of the player controls, as a hexadecimal value such as `00adef`. The embed settings of the video on Vimeo may override it. |
| **Loop** | Starts the video again when it ends. |
| **Muted** | Mutes the video when it loads. Some browsers only autoplay muted videos. |
| **Plays inline** | Plays the video inline on mobile devices. Unchecked, the video goes fullscreen when it starts. |
| **Speed** | Shows speed controls in the player's preferences menu. Vimeo offers them only for videos of some paid Vimeo plans. |
| **Transparent** | Keeps the player background transparent. |

With Video Time Pro installed, the page has four more options: **Do not track**, **Autopause**,
**Background** and **Picture-in-picture**.

Your administrator can lock options for the whole site. A locked option is replaced by a line such
as "Controls is globally forced to: Yes". Options that your administrator marked as advanced appear
after **Show more...**.

## What participants see

The video plays in Vimeo's player on the activity page, with the activity's notes below it.
