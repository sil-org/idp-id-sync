#!/usr/bin/env bash

# Try to install composer dev dependencies
cd /data
composer install --no-interaction --no-scripts

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

# Start apache
apachectl start

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

# Run the feature tests (skipping integration tests)
./vendor/bin/behat --config=features/behat.yml --tags '~@integration'

# If they failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi
