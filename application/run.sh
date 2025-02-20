#!/usr/bin/env bash

if [[ $PARAMETER_STORE_PATH ]]; then
  config-shim --path $PARAMETER_STORE_PATH /data/yii batch/full
elif [[ $APP_ID ]]; then
  config-shim --app $APP_ID --config $CONFIG_ID --env $ENV_ID /data/yii batch/full
else
  /data/yii batch/full
fi
