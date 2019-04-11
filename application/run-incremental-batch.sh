#!/bin/bash

logger -p 1 -t batch.warning "{\"message\": \"Preparing to run ID Sync batch\", \"batch\": \"incremental\", \"idp_name\": \"${IDP_NAME}\", \"app_env\": \"${APP_ENV}\", \"id_store_adapter\": \"${ID_STORE_ADAPTER}\"}"

output=$(/data/yii batch/incremental 2>&1)

# If it failed, exit.
rc=$?;
if [[ $rc != 0 ]]; then
  echo $output;
  logger -p 1 -t batch.warning "{\"message\": \"FAILED: ID Sync batch. Exit code ${rc}. Message: ${output}\", \"batch\": \"incremental\", \"idp_name\": \"${IDP_NAME}\", \"app_env\": \"${APP_ENV}\", \"id_store_adapter\": \"${ID_STORE_ADAPTER}\"}"
  exit $rc;
fi

logger -p 1 -t batch.warning "{\"message\": \"Ran ID Sync batch\", \"batch\": \"incremental\", \"idp_name\": \"${IDP_NAME}\", \"app_env\": \"${APP_ENV}\", \"id_store_adapter\": \"${ID_STORE_ADAPTER}\"}"
