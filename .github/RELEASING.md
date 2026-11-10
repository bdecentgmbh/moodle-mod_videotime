# Releasing Video Time

How Video Time, Video Time Pro and Video Time Premium are released, for their maintainers. This file is in `.github`, so
it is neither in the release ZIPs and bundles nor in the Documentation Center (tool_mdocs) of the sites that install
them.

A release is the same tag, e.g. `v1.13-r1`, in this repository, in moodle-mod_videotime_pro and in
moodle-mod_videotime_repository. `<release>` is `$plugin->release` in `version.php`, the same in all three.

| What | Published to | By |
| --- | --- | --- |
| Video Time | The GitHub release of this repository, the Moodle plugins directory, the Video Time product on bdecent.de | The [Release workflow](workflows/release.yml), when the GitHub release is published |
| Video Time Pro | The stable branches and releases of [video-time-pro](https://github.com/bdecentgmbh/video-time-pro), the Video Time Pro product on bdecent.de | *Publish Video Time Pro* in video-time-pro, started by hand |
| Video Time Premium | The stable branches and releases of [video-time-premium](https://github.com/bdecentgmbh/video-time-premium), the Video Time Premium product on bdecent.de | *Publish Video Time Premium* in video-time-premium, started by hand |
| Video Time with Pro and Premium as git submodules | The branch `MOODLE_404_PREMIUM` of this repository | By hand, see step 6 |

Pro and Premium are never published to the Moodle plugins directory. moodle-mod_videotime_pro and
moodle-mod_videotime_repository only run CI; their code reaches customers through the two bundle repositories.

## Steps

1. In all three repositories: set `$plugin->release` (e.g. `1.13`) and `$plugin->version` in `version.php`, and add a
   section `## <release> (<version>)` with the changes at the top of `CHANGES.md`, e.g. `## 1.13 (2026090102)`. Then
   merge `dev` into `master` with a pull request. Moodle Plugin CI runs on the merge commit in `master`, and step 3
   waits for that run.
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
6. Update `MOODLE_404_PREMIUM` (see below).
7. Edit and publish the draft release note on bdecent.de.

The bundle workflows stop before they push anything if the Video Time release of the tag has no ZIP yet (its release
workflow has not passed), if an add-on is of another release or needs a newer Video Time, or if the tag exists in the
bundle repository already. See the READMEs of video-time-pro and video-time-premium.

## MOODLE_404_PREMIUM

This branch holds Video Time with the add-ons as git submodules: `plugin/pro` (moodle-mod_videotime_pro) and
`plugin/repository` (moodle-mod_videotime_repository). Unlike the bundles, it can only be cloned with access to the
two private repositories. It gets no tag and no GitHub release.

After step 2, merge `master` into it and point the submodules to the tag. The branch has no workflows, because CI
can't check out the private submodules. So whenever `dev` has changed a workflow file, the merge stops on a conflict
that GitHub's web editor can't resolve: keep the workflows deleted. Do it on a branch with a pull request into
`MOODLE_404_PREMIUM`:

```sh
git fetch origin --tags
git switch -c premium-1.13 origin/MOODLE_404_PREMIUM
git merge --no-ff --no-commit origin/master
git rm -q --ignore-unmatch .github/workflows/moodle-ci.yml .github/workflows/release.yml
git commit -m "Merge master into MOODLE_404_PREMIUM"
git submodule update --init
git -C plugin/pro fetch --tags && git -C plugin/pro checkout v1.13-r1
git -C plugin/repository fetch --tags && git -C plugin/repository checkout v1.13-r1
git add plugin/pro plugin/repository
git commit -m "Update for release 1.13"
git push origin premium-1.13
```

## Further releases, pre-releases, re-runs

**Further releases of the same version** (R2, R3 …): merge the fixes into `master` as above, raise `$plugin->version`,
add a new section at the top of CHANGES.md with the same release and the new version (e.g. `## 1.13 (2026090103)`),
tag `v1.13-r2`, and update `MOODLE_404_PREMIUM` again.

**Pre-releases:** tick *Set as a pre-release* and use a tag `v<release>-<suffix>` from any branch, e.g. `v1.14-rc1`
on `dev` (earlier release candidates were tagged on `MOODLE_404_PREMIUM`). The ZIP goes to staging.bdecent.de only,
not to the plugins directory. For a bundle of a pre-release, tag the add-ons too; it is a pre-release as well and also
goes to staging only. Unticking the box later runs the release steps.

**Running a step again:** *Actions > Release > Run workflow* with the tag, optionally as a dry run. Products on
bdecent.de that already have the tag are skipped.

**Totara** builds (tags such as `v1.8.2-t18` or `v1.5t`) are not published by these workflows; the release workflow
stops on them. Publish them by hand in the product's GitHub tab on bdecent.de.

## CI

[Moodle Plugin CI](workflows/moodle-ci.yml) runs for pull requests and for pushes to `master`, as in the other bdecent
plugins: a branch is tested through its pull request, which also tests the merge result, and nothing runs twice. Runs
can also be started by hand (*Actions > Moodle Plugin CI > Run workflow*).

## Secrets

| Repository | Secret | Purpose |
| --- | --- | --- |
| moodle-mod_videotime | `MOODLE_MARKETPLACE_TOKEN` | API token of a maintainer of Video Time in the Moodle plugins directory |
| moodle-mod_videotime, video-time-pro, video-time-premium | `BDECENT_RELEASE_SECRET`, `BDECENT_STAGING_AUTH` | The release webhook of bdecent.de and the basic auth of staging.bdecent.de |
| video-time-pro, video-time-premium | `VIDEOTIME_READ_TOKEN` | Reads the private add-on repositories |
| moodle-mod_videotime_repository | `PRO_TOKEN` | Lets its CI clone moodle-mod_videotime_pro |
