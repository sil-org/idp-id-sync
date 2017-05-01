#!/bin/bash

logger -p 1 -t batch.warning "{\"message\": \"Preparing to run ID Sync batch\", \"batch\": \"full\", \"app_id\": \"${APP_ID}\", \"app_env\": \"${APP_ENV}\"}"

/data/yii batch/full

logger -p 1 -t batch.warning "{\"message\": \"Ran ID Sync batch\", \"batch\": \"full\", \"app_id\": \"${APP_ID}\", \"app_env\": \"${APP_ENV}\"}"
