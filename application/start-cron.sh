#!/usr/bin/env bash

# Dump env to a file
touch /etc/cron.d/idsync
env | while read line ; do
   echo "$line" >> /etc/cron.d/idsync
done

# Add env vars to idsync-cron to make available to scripts
cat /etc/cron.d/idsync-cron >> /etc/cron.d/idsync

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

# Remove original cron file without env vars
rm -f /etc/cron.d/idsync-cron

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

# Start cron daemon in the background
service cron start

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi
