#!/bin/bash

echo "{\"message\": \"Preparing to run ID Sync batch\", \"batch\": \"full\", \"idp_name\": \"${IDP_NAME}\", \"app_env\": \"${APP_ENV}\", \"id_store_adapter\": \"${ID_STORE_ADAPTER}\"}"

/data/yii batch/full 2>&1

# If it failed, exit.
rc=$?;
if [[ $rc != 0 ]]; then
  echo "{\"message\": \"FAILED: ID Sync batch. Exit code ${rc}.\", \"batch\": \"full\", \"idp_name\": \"${IDP_NAME}\", \"app_env\": \"${APP_ENV}\", \"id_store_adapter\": \"${ID_STORE_ADAPTER}\"}"
  exit $rc;
fi

echo "{\"message\": \"Ran ID Sync batch\", \"batch\": \"full\", \"idp_name\": \"${IDP_NAME}\", \"app_env\": \"${APP_ENV}\", \"id_store_adapter\": \"${ID_STORE_ADAPTER}\"}"
