#!/usr/bin/env bash

# Try to install composer dev dependencies
cd /data
composer install --no-interaction --no-scripts --no-progress

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

# Start apache
apachectl start

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

# Make sure the database is ready, then wait a little bit longer so that apache
# (in "broker") has time to come up.
whenavail brokerdb 3306 60 echo Waited for brokerdb
sleep 15

# Run the unit tests
./vendor/bin/phpunit

# If they failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

# Run the feature tests (skipping integration tests)
./vendor/bin/behat --config=features/behat.yml --tags '~@integration' --stop-on-failure

# If they failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi
