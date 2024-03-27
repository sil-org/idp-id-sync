#!/usr/bin/env bash

if [[ -z "${APP_ID}" ]]; then
  /data/yii batch/full
else
  config-shim --app $APP_ID --config $CONFIG_ID --env $ENV_ID /data/yii batch/full
fi
