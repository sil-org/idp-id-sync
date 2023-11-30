#!/usr/bin/env bash

# Exit the script if any command fails
set -e

# Print the script text as each line is executed
set -x

# Run a sync
/data/yii batch/full
