#!/usr/bin/env bash

# Dump env to a file to make available to cron
env >> /etc/environment

# Start cron daemon in the background
service cron start

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi
