#!/usr/bin/env bash

# Dump env to a file
touch /etc/cron.d/idsync
env | while read line ; do
   echo "$line" >> /etc/cron.d/idsync
done

# Add env vars to idsync-cron to make available to scripts
cat /etc/cron.d/idsync-cron >> /etc/cron.d/idsync

# Remove original cron file without env vars
rm -f /etc/cron.d/idsync-cron

# Start cron daemon in the background
service cron start
