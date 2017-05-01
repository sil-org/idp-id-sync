#!/bin/bash

logger -p 1 -t batch.warning "{\"message\": \"Preparing to run ID Sync batch\", \"batch\": \"incremental\", \"idp_name\": \"${IDP_NAME}\", \"app_env\": \"${APP_ENV}\"}"

/data/yii batch/incremental

logger -p 1 -t batch.warning "{\"message\": \"Ran ID Sync batch\", \"batch\": \"incremental\", \"idp_name\": \"${IDP_NAME}\", \"app_env\": \"${APP_ENV}\"}"
