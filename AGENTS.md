# AGENTS.md

This file provides guidance to coding agents like Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A PHP 8.3 / Yii2 console app that reads users from an "ID Store" (an HR system: Workday, Google Sheets, SecureUser) and syncs them into the IdP "ID Broker" (`sil-org/idp-id-broker`) over its API. It runs as a batch job in Docker. There is no web UI.

## Commands

Everything runs through Docker Compose. Run commands from the repo root. The PHP project lives in `application/`, which is mounted at `/data` in the containers.

- `make testci`: installs deps, starts `broker` + `brokerdb`, and runs PHPUnit plus every Behat suite except `@integration`. This is what CI runs.
- `make test`: the full local run, which includes the integration tests. Those need real Google Sheets/Workday credentials in `local.env` (see README).
- `make unittest`: PHPUnit only (`vendor/bin/phpunit _tests`).
- `make psr2`: a php-cs-fixer **dry run** (CI runs it too). To fix formatting, run `vendor/bin/php-cs-fixer fix .` inside the `cli` container.
- `make bash`: a shell in the `cli` container. Run `make testci` (or `make broker`) first so the broker is up.
- Single Behat suite: `docker compose run --rm cli bash -c "vendor/bin/behat --suite=sync_features"`. Suite names are in `application/behat.yml`.
- Single PHPUnit test: `docker compose run --rm cli vendor/bin/phpunit --filter <testName> _tests`.
- `make sync_full` / `make sync_incremental`: run a real sync with `local.env` config.
- `make adminer`: a DB browser for the broker DB at http://localhost:8001.

## Architecture

- **Entry point**: `console/controllers/BatchController.php`. `yii batch/full` (the default container command, via `run.sh`) and `yii batch/incremental` (users changed in the last 11 minutes, then a full sync). Both send Sentry cron check-ins and a heartbeat (`Monitor`). `run.sh` wraps the command in `config-shim` when AWS Parameter Store/AppConfig env vars are set.
- **Core logic**: `common/sync/Synchronizer.php`. A full sync gets all active ID Store users and all broker users keyed by `employee_id`. It creates the ones missing from the broker, updates and activates the ones in both, and deactivates the broker users missing from the store. A **safety cutoff** (`SYNC_SAFETY_CUTOFF`, default 0.15 of active broker users, minimum 10, unlimited when the broker has fewer than 10 active users) aborts the run if creates or deactivates would exceed it.
- **Adapters** are pluggable Yii components set up in `common/config/main.php`:
  - `idStore`: picked by `ID_STORE_ADAPTER` (`workday`, `googlesheets`, `secureuser`, `fake`) through `IdStoreBase::getAdapterClassFor()`. Each adapter implements `getFieldNameMap()`, which maps the store's field names to the internal `User` constants. `IdStoreBase::getAsUser(s)` uses it to translate.
  - `idBroker`: picked by `ID_BROKER_ADAPTER` (`idp` or `fake`) through `IdBrokerBase`.
  - Adapter properties come from env-var prefixes: `ID_STORE_CONFIG_*` and `ID_BROKER_CONFIG_*` (`Env::getArrayFromPrefix`).
  - To add an adapter, implement the interface in `common/interfaces/`, extend the base class, and register it in the base class's `$adapters` map.
- **`common/models/User.php`**: the normalized user record that moves between store and broker. Field-name constants match the broker's API fields.
- **Notifier**: `common/components/notify/`. `EmailServiceNotifier` sends HR "missing email" and "new user" notices through the broker's email service. The templates are in `common/mail/`.
- **Fakes** for tests: `common/components/adapters/fakes/` and `FakeEmailNotifier`.

## Tests

- Behat contexts live in `_tests/bootstrap/`, autoloaded as `Sil\Idp\IdSync\Behat\Context\`. Features live in `_tests/_features/`.
- `id-store-integration.feature` is shared by several suites (Google Sheets, Workday, SecureUser). Each suite uses tag filters in `behat.yml` to skip the scenarios its store doesn't support.
- `idp-id-broker-integration` runs against the real broker container started by Compose. The other non-integration suites use the fakes.

## Code style

- One class per file, named after the class (PSR-4), including in tests.

## Configuration

Env vars are documented in `local.env.dist`. Copy it to `local.env` (gitignored, and Claude is denied read access to it). Google integration tests also expect `application/google-auth.json`, which is gitignored and also denied to Claude.
