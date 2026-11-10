# Video Time — Developer guide

How the Video Time activity (`mod_videotime`) is built, for people changing it or writing a player
or tab plugin for it. The components are listed in the [readme](README.md).

## Architecture

`mod_videotime` holds the activity record, the settings form, text tracks, the tab framework and
the web services. It does not play videos itself: players and most features come from subplugins
of two types, declared in `db/subplugins.json`.

| Type | Directory | Plugin info class | Purpose |
| --- | --- | --- | --- |
| `videotimeplugin` | `mod/videotime/plugin` | `\mod_videotime\plugininfo\videotimeplugin` | Players (`vimeo`, `videojs`, `live`) and extensions (`pro`, `repository`) |
| `videotimetab` | `mod/videotime/tab` | `\mod_videotime\plugininfo\videotimetab` | Tabs shown next to the video |

A subplugin is active when its config value `enabled` is set (`get_config('videotimeplugin_vimeo',
'enabled')`); `sortorder` sets its position. `\mod_videotime\plugin_manager` reads and changes
both (`get_sorted_plugins_list()`, `show_plugin()`, `hide_plugin()`, `move_plugin()`) and draws
the manage pages (`adminmanageplugins.php?subtype=…`). A subplugin that should be on after a new
installation enables itself in `db/install.php`:

```php
function xmldb_videotimetab_example_install() {
    $manager = new \mod_videotime\plugin_manager('videotimetab');
    $manager->show_plugin('example');
    return true;
}
```

Video Time Pro (`videotimeplugin_pro`) and Video Time Premium (`videotimeplugin_repository`) are
`videotimeplugin` subplugins in separate repositories, installed into `plugin/pro` and
`plugin/repository`; `.gitignore` excludes both folders. The core checks for them with
`videotime_has_pro()` and `videotime_has_repository()` (installed, enabled, and not switched off
with `$CFG->disable_videotime_pro` or `$CFG->disable_videotime_repository`; Premium also needs
Pro). Features gated this way include the custom completion rules, grading
(`FEATURE_GRADE_HAS_GRADE`), questions (`FEATURE_USES_QUESTIONS`), `report.php`, label and preview
mode on the course page, resume time and the next activity button.

## The instance

`\mod_videotime\videotime_instance` wraps a `videotime` record and is what the pages render.

- `videotime_instance::instance_by_id($id)` loads the record and passes it through the
  `load_settings` callback of every `videotimeplugin`, so the record also carries the settings the
  subplugins store in their own tables. Fields are read as properties (`$instance->label_mode`);
  `to_record()` returns a copy of the merged record.
- `get_force_settings()` and `is_field_forced()` report site-wide forced settings: the config value
  `videotime/forced` (comma-separated field names) plus the `forced_settings` callbacks of the subplugins.
- `embed_player($record)` asks each `videotimeplugin` for a player (`embed_player` callback); the
  first one that returns a renderable is used.
- The display modes are the constants `NORMAL_MODE` (0), `LABEL_MODE` (1) and `PREVIEW_MODE` (2) of
  the field `label_mode`, which Pro stores.
- Rendering goes through `\mod_videotime\output\renderer`: a templatable widget is rendered with the
  template named after its class, in the component its `get_component_name()` returns, or
  `mod_videotime`. The instance uses `templates/videotime_instance.mustache`, tabs
  `templates/tabs.mustache`.

`player.php` (the page the Moodle app embeds) lets other components replace the instance and the
renderer: a class in a component's namespace `videotime\instance` that extends
`videotime_instance` and has the static methods `get_instance($id)` and `get_renderer($id)` is
asked first.

## Player and extension plugins (`videotimeplugin`)

The core calls these functions in the plugin's `lib.php` through `component_callback()`. All are
optional.

| Callback | Called from | Purpose |
| --- | --- | --- |
| `add_form_fields($mform, $formclass)` | `mod_form.php`, the players' options forms | Add fields; `$formclass` is `mod_videotime_mod_form` or the class of the options form |
| `definition_after_data($mform, $context)` | `mod_form.php` | Change the form after the data is set |
| `validation($data, $files)` | `mod_form.php` | Return an array of errors |
| `data_preprocessing(array &$defaultvalues, int $instance)` | `mod_form.php`, `options.php` | Load the plugin's settings into the form; `$instance` is 0 for a new activity |
| `update_instance($moduleinstance, $mform)` | `videotime_add_instance()`, `videotime_update_instance()`, `options.php` | Save the plugin's settings |
| `delete_instance($id)` | `videotime_delete_instance()` | Delete the plugin's data |
| `load_settings($instance)` | `videotime_instance` | Return the record with the plugin's settings merged in |
| `forced_settings($instance, $forced)` | `videotime_instance` | Return the forced settings, adding the plugin's |
| `embed_player($instance)` | `view.php`, `player.php`, `options.php`, `videotime_instance` | Return a renderable player, or null if the plugin does not play this video |
| `setup_page($instance, $cm)` | `view.php` through `videotime_instance::setup_page()` | Add page requirements |
| `get_file_areas($course, $cm, $context)` | file browsing | The plugin's file areas in the activity |
| `config_file_areas()` | backup and restore | File areas to include in activity backups |

`videotimeplugin_<name>_extend_settings_navigation($settings, $node)` in `lib.php`, if present,
is called from `videotime_extend_settings_navigation()`.

A player usually also has:

- a renderable that extends `\mod_videotime\vimeo_embed` (for example
  `\videotimeplugin_vimeo\video_embed`);
- `\videotimeplugin_<name>\form\options`, the form of the **Embed options** page (`options.php`)
  for the activities the player plays;
- `backup/moodle2/backup_videotimeplugin_<name>_subplugin.class.php` and the restore class, with
  `define_videotime_subplugin_structure()`;
- `settings.php`, which becomes an admin page in the category *Video Time Plugins*.

## Tab plugins (`videotimetab`)

Each tab plugin must have the class `\videotimetab_<name>\tab`, extending
`\mod_videotime\local\tabs\tab`. `\mod_videotime\local\tabs\tabs` creates one object per tab plugin
in the order of the manage page and renders those whose `is_visible()` is true. The core calls
`data_preprocessing()`, `save_settings()` and `delete_settings()` for every installed tab plugin,
enabled or not, and `add_form_fields()` for the enabled ones.

| Method | Required | Purpose |
| --- | --- | --- |
| `get_tab_content(): string` | Yes | The HTML of the tab panel |
| `add_form_fields($mform)` (static) | No | Fields in the **Tabs** section; the default adds the checkbox `enable_<name>` |
| `data_preprocessing(array &$defaultvalues, int $instance)` (static) | No | Load the tab's settings into the form |
| `save_settings(stdClass $data)` (static) | No | Save them when the activity is saved |
| `delete_settings(int $id)` (static) | No | Delete them with the activity |
| `get_config_file_areas(): array` (static) | No | File areas to include in activity backups |
| `added_dependencies()` (static) | No | `''` when the tab can be used, otherwise an HTML message for the **Status** column; the tab is then disabled |
| `is_visible(): bool` | No | Whether the tab shows in this activity; default `is_enabled()` |
| `get_label(): string` | No | The tab name; default the string `label` of the plugin |
| `setup_page()` | No | Add page requirements |

By convention a tab stores its settings in a table `videotimetab_<name>` with a field
`videotime`, which `get_record()` reads, and its `is_visible()` checks that the activity has a row
there. The plugin needs the strings `pluginname` and `label`; the default `add_form_fields()` uses
the config value `default` for the initial state. The **Watch** tab is marked persistent with
`set_persistent()`: the player stays in its panel at full width. In the other panels the tab
content is next to the player.

## Callbacks and hooks of the activity

`lib.php` implements the standard module callbacks (`videotime_supports()`, add, update and delete
instance, grade functions, `mod_videotime_pluginfile()`, `videotime_get_coursemodule_info()`,
`videotime_cm_info_view()`, `videotime_cm_info_dynamic()`, settings and course navigation,
calendar actions, drag and drop, file browsing). `classes/courseformat/overview.php` adds the
**View report** action to the activity overview of Moodle 5.0 and later.

Two hooks of the Moodle Hooks API let plugins create activities from files dropped on the course
page; the core defines no callbacks for them, so register them in your `db/hooks.php`:

| Hook | Use |
| --- | --- |
| `\mod_videotime\hook\dndupload_register` | Call `register_handler($extension)` for each file extension you handle |
| `\mod_videotime\hook\dndupload_handle` | Create the activity from `get_draftitemid()`, `get_course()`, `get_coursemodule()`, `get_displayname()` and call `set_instanceid()` |

The *Video Time VideoJS Player* implements both.

## Web services

| Function | Type | Purpose |
| --- | --- | --- |
| `mod_videotime_get_videotime` | read | Returns the instance record of a course module (`cmid`), including the subplugin settings, as described by `videotime_instance::get_external_description()` |
| `mod_videotime_view_videotime` | write | Triggers `course_module_viewed` and marks the activity viewed for completion (`cmid`) |

Both are available through AJAX and in the services `moodle_mobile_app` and `local_mobile`. The
session and watch-time functions the JavaScript calls (`videotimeplugin_pro_get_new_session`,
`videotimeplugin_pro_record_watch_time` and others) belong to Video Time Pro.

## Events

- `\mod_videotime\event\course_module_viewed`: on `view.php` and through `mod_videotime_view_videotime`.
- `\mod_videotime\event\course_module_instance_list_viewed`: on `index.php` (Moodle 4.4 and 4.5;
  later versions redirect to the activity overview first).
- `\mod_videotime\event\settings_observer` observes `\core\event\admin_settings_changed`. When a
  setting `videotime/<field>_force` is turned on, it copies the default of `<field>` into all
  activities. Current versions store forced settings in `forced` instead, so this applies only to
  old configurations.

## JavaScript

AMD modules are in `amd/src`; the minified files in `amd/build` are committed. Build them with
Moodle's Grunt: use the Node version from Moodle's `.nvmrc` (`nvm use` in the Moodle root) and
run `npx grunt amd` in `mod/videotime`.

| Module | Purpose |
| --- | --- |
| `mod_videotime/videotime` | The `VideoTime` class: creates the Vimeo player, tracks playing, pausing and the end, resumes, and sends the viewing data through the Pro web services |
| `mod_videotime/videotime-plugin` | `VideoTimePlugin`, the base class of JavaScript plugins |
| `mod_videotime/player` | Vimeo Player API (`@vimeo/player` 2.29.7, MIT), listed in `thirdpartylibs.xml` |
| `mod_videotime/resize_tab_player` | Places the player in the active tab, the resizable divider, and links with `data-action="cue"` and `data-start` that move the video |
| `mod_videotime/mod_form` | With Pro: shows the Pro help text (`modulenamepro_help`) in the help popover of the form heading |

A player template creates `new VideoTime(elementId, cmId, hasPro, interval, instance)`, registers
plugins with `registerPlugin()` and calls `initialize()`. The VideoJS and Live players extend
`VideoTime` for their own players. A plugin extends `VideoTimePlugin` and may implement
`initialize(videotime, instance)`, `getCurrentPosition(position)`, `handlePause()`, `handleEnd()`,
`startWatchInterval()` and `setCurrentTime(sessionId, seconds)`; Pro's plugins are examples.
`VideoTime` also reads the URL parameter `time` (a WebVTT time such as `00:01:30.000`) as start
time, and the URL parameter `active` of `view.php` (a tab name, default `watch`) chooses the open
tab.

`appjs/` holds the JavaScript for the Moodle app (`db/mobile.php`, `\mod_videotime\output\mobile`),
with an older Vimeo Player API (2.10.0).

## Database

| Table | Content |
| --- | --- |
| `videotime` | One row per activity: name, description, video URL (`vimeo_url`), notes (`video_description`), the completion rules, `show_description_in_player`, `viewpercentgrade` and `enabletabs` |
| `videotime_track` | The text tracks uploaded in the activity form: type (`kind`), label, language (`srclang`), visibility and default flag |

Players, tabs, Pro and Premium keep their per-activity settings and user data in their own tables.

File areas of `mod_videotime` in the module context: `intro` and `video_description` (item id 0)
and `texttrack` (item id = `videotime_track.id`).

## Integrations

- Moodle app: `db/mobile.php`; the setting `videotime/mobileiframe` switches to `player.php` in an
  iframe, signed in with the user's token for the official mobile service.
- Global search: `\mod_videotime\search\activity` and `\mod_videotime\search\texttrack`.
- Dash: `\mod_videotime\local\block_dash\videotime_stats_data_source` with the table
  `\mod_videotime\local\dash_framework\structure\videotime_table`; Dash finds it by its namespace.

## Tests

- PHPUnit tests are in `tests/`: `lib_test.php` (calendar event actions, deleting text tracks) and
  `external_test.php` (the two web services). Run them with
  `vendor/bin/phpunit --testsuite mod_videotime_testsuite`.
- Behat features are in `tests/behat` (`videotime_notes.feature`, `videotime_tab.feature`), tagged
  `@mod_videotime`. `behat_mod_videotime.php` adds the page `"<activity name>" "mod_videotime >
  Embed options"`.
- The data generator (`tests/generator/lib.php`) creates activities with default values for the
  activity and player fields (the video URL defaults to a Vimeo address). It has
  `create_texttrack($cmid, $options)`, which writes a WebVTT file from `cues`, and
  `create_session($cmid, $options)`, which needs Pro.

The subplugins have their own tests in their `tests` folders.

## Continuous integration

`.github/workflows/moodle-ci.yml` runs on every push and pull request with `moodle-plugin-ci` 4. The
matrix covers Moodle 4.4 (PHP 8.2, MariaDB), 4.5 (PHP 8.2, PostgreSQL), 5.0 and 5.1 (PHP 8.3,
MariaDB), 5.2 (PHP 8.4, PostgreSQL) and `main` (PHP 8.4 with PostgreSQL, PHP 8.3 with MariaDB).
It runs PHP lint, PHP Mess Detector, the code checker with `--max-warnings 0`, the PHPDoc checker,
validation, the upgrade savepoint check, Mustache lint, Grunt, PHPUnit and Behat (Chrome). The CI
checks out only this repository, so it runs without Pro and Premium.
