#!/usr/bin/env bash

# Exit the script if any command fails
set -e

# Print the script text as each line is executed
set -x

# Try to install composer dev dependencies
cd /data
composer install --no-interaction --no-scripts --no-progress

# Make sure the database is ready, then wait a little bit longer so that apache
# (in "broker") has time to come up.
whenavail brokerdb 3306 60 echo Waited for brokerdb
sleep 15

# Run the unit tests
./vendor/bin/phpunit

# Run the feature tests (skipping integration tests)
./vendor/bin/behat --config=features/behat.yml --tags '~@integration' --stop-on-failure
