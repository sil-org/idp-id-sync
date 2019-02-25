# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added:
- Added 'groups' and 'personal_email' attributes

## [2.1.1] - 2019-02-15
### Fixed:
- Wait to update `last_synced` until after actually syncing.
- Reduce likelihood of hitting rate-limit when syncing with Google Sheets by
  updating `last_synced` values in batches.

## [2.1.0] - 2018-08-09
### Added:
- ID Store adapter for Workday

### Fixed:
- Treat `'0'` and `0` as `'no'` and treat `'1'` and `1` as `'yes'` (as values
  for `locked` and `require_mfa`).
- Ignore unexpected ID Store fields, rather than crashing.

## [2.0.0] - 2018-07-23
### Changed
- Sync all values provided by ID Store to ID Broker (even null values). This
  lets fields like `spouse_email` be emptied in ID Broker if they are emptied in
  ID Store.

### Fixed
- `Synchronizer->activateAndUpdateUser()` now forces `active` to be `yes` rather
  than allowing ID Store to provide a different `active` value to ID Broker when
  **activating** and updating a user.

## [1.2.0] - 2018-07-18
### Added
- Sync spouse and manager emails

## [1.1.3] - 2018-05-18
### Added
- Add support for `COOKIE_VALIDATION_KEY` env. var (to satisfy Yii)

### Fixed
- Update Yii to 2.0.15.1

## [1.1.2] - 2017-11-28
### Fixed
- Small bugfixes
- Fix PSR-2 formatting (and automate checking against PSR-2)

## [1.1.1] - 2017-11-28
### Fixed
- Handle "true"/"false" strings for require_mfa.

## [1.1.0] - 2017-11-28
### Added
- Also sync require_mfa

### Fixed
- Only dump env to /etc/environment for cron

## [1.0.1] - 2017-09-15
### Fixed
- Allow safety cutoffs above 100% (1.00).

## [1.0.0] - 2017-08-31
### Added
- EmailServiceTarget (Yii log target), emailing error-level log messages to the
  email address in an `ALERTS_EMAIL` environment variable, if provided.

## [0.6.0] - 2017-08-23
### Added
- Have site/system-status confirm that the Notifier has whatever config values
  it will need.

### Changed
- Require new env. vars for Email Service if a `NOTIFIER_EMAIL_TO` is provided.
- Switch from using EmailNotifier to EmailServiceNotifier for sending
  notification emails to HR.
- Switch from using `silinternational/yii2-jsonsyslog` for JsonSyslogTarget to
  using the version in `silinternational/yii2-json-log-targets`.
- Render both an HTML and a text version of the HR notification email.

## [0.5.0] - 2017-08-09
### Added
- Add safety cutoff to prevent massive changes (e.g. - due to bad API
  responses).

## [0.4.0] - 2017-08-02
### Added
- Use `IDP_DISPLAY_NAME` environment variable (if present) for name shown to
  users.
- Log the number of users in ID Broker that were already inactive, and thus not
  changed.

### Changed
- Adjust default cron schedule to NOT skip an hour for incremental syncs
  (previously done to avoid conflicts with full sync) and offset full sync by
  2 minutes to land between incremental sync runs.

### Fixed
- Remove any trailing slash (`/`) from the ID Store's `baseUrl` to prevent a
  double slash (`//`) from resulting in incorrect API responses.

## [0.3.0] - 2017-06-16
### Changed
- Switch to pulling in Google auth JSON from a file (for Google Sheets adapter
  for ID Store).

## [0.2.0] - 2017-06-15
### Added
- Add Insite adapter for ID Store.
- Push/tag docker image for every branch (not just `master`).
- Add cron job that runs full- and incremental-syncs at regular intervals.
- Add `User` model for better internal handling of user info.
- Log more events encountered during a synchronization, including aggregated
  stats.
- Better log/report errors from incremental-/full-sync.
- Add Google Sheets adapter for ID Store.

### Changed
- Change IdStoreInterface's `getActiveUsersChangedSince()` to
  `getUsersChangedSince()`, since the list of changed users can include
  removed (deactivated) users.
- Refactor for new IdBrokerClient method signatures.
- Change IdStoreInterface to return `User` objects, not simply arrays.
- Rename env. variables for ID Store and ID Broker adapters.

### Fixed
- Handle sync errors gracefully, and continue with sync if possible.
- Fix handling of non-boolean values for `locked`, including "no" and "false"
  (case-insensitive).
- Better communicate when required env. variables are missing.
- Enable sending HR a notification email when ID Store users lack an email
  address.

## 0.1.0 - 2017-04-10
### Added
- First release.

[Unreleased]: https://github.com/silinternational/idp-id-sync/compare/2.1.1...develop
[2.1.1]: https://github.com/silinternational/idp-id-sync/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/silinternational/idp-id-sync/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/silinternational/idp-id-sync/compare/1.2.0...2.0.0
[1.2.0]: https://github.com/silinternational/idp-id-sync/compare/1.1.3...1.2.0
[1.1.3]: https://github.com/silinternational/idp-id-sync/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/silinternational/idp-id-sync/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/silinternational/idp-id-sync/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/silinternational/idp-id-sync/compare/1.0.1...1.1.0
[1.0.1]: https://github.com/silinternational/idp-id-sync/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/silinternational/idp-id-sync/compare/0.6.0...1.0.0
[0.6.0]: https://github.com/silinternational/idp-id-sync/compare/0.5.0...0.6.0
[0.5.0]: https://github.com/silinternational/idp-id-sync/compare/0.4.0...0.5.0
[0.4.0]: https://github.com/silinternational/idp-id-sync/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/silinternational/idp-id-sync/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/silinternational/idp-id-sync/compare/0.1.0...0.2.0
