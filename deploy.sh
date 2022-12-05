#!/bin/bash

STAGE=$1
NOW=$(date +%s)
DEPLOY_NAME="deploy.$NOW.tar.gz"
ENV_FILE_DEPLOY=".env.deploy.$STAGE"

export $(grep -v '^#' "$ENV_FILE_DEPLOY" | xargs)

echo
echo "Deploying to $STAGE..."
date
echo

if [ $STAGE = 'local' ]; then
  cd "$LOCAL_PATH"
  tar -czf "$DEPLOY_NAME" "$THEME_NAME"
  lftp -e "put -O $REMOTE_PATH $DEPLOY_NAME; bye" --user $SSH_USER --password $SSH_PASS sftp://$SSH_HOST
  rm $DEPLOY_NAME
fi

OUT=$?

if [ $OUT != 0 ]; then
  echo 'Deploy failed'
  exit 1
fi


echo 'Transfer successful, moving to target...'

if [ $STAGE = 'local' ]; then
  ssh -t $SSH_USER@$SSH_HOST << EOF
  cd "$REMOTE_PATH"
  mv "$THEME_NAME" "$THEME_NAME.$NOW"
  tar -xzf "$DEPLOY_NAME"
  echo "$SSH_PASS" | sudo -S chown -R www-data:www-data "$THEME_NAME"
  rm "$DEPLOY_NAME"
  exit
EOF
fi

echo 'Deploy successful'

date
echo

