#!/usr/bin/env bash

# Exit the script if any command fails
set -e

# Print the script text as each line is executed
set -x

# Try to install composer dev dependencies
cd /data
composer install --no-interaction --no-scripts --no-progress

# Run the unit tests
./vendor/bin/phpunit

# Run the feature tests (skipping integration tests)
./vendor/bin/behat --config=features/behat.yml --tags '~@integration' --stop-on-failure
