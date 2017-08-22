# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [0.6.0] - 2017-08-22
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

## [0.1.0] - 2017-04-10
### Added
- First release.

[Unreleased]: https://github.com/silinternational/idp-id-sync/compare/0.6.0...develop
[0.6.0]: https://github.com/silinternational/idp-id-sync/compare/0.5.0...0.6.0
[0.5.0]: https://github.com/silinternational/idp-id-sync/compare/0.4.0...0.5.0
[0.4.0]: https://github.com/silinternational/idp-id-sync/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/silinternational/idp-id-sync/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/silinternational/idp-id-sync/compare/0.1.0...0.2.0
