# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

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

## [0.2.0] - 2017-06-15

## [0.1.0] - 2017-04-10

[Unreleased]: https://github.com/silinternational/idp-id-sync/compare/0.5.0...develop
[0.5.0]: https://github.com/silinternational/idp-id-sync/compare/0.4.0...0.5.0
[0.4.0]: https://github.com/silinternational/idp-id-sync/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/silinternational/idp-id-sync/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/silinternational/idp-id-sync/compare/0.1.0...0.2.0
