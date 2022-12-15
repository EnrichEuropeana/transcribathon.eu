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

cd "$LOCAL_PATH"
tar -czf "$DEPLOY_NAME" "$THEME_NAME"

if [ $STAGE = 'local' ]; then
  lftp -e "put -O $REMOTE_PATH $DEPLOY_NAME; bye" --user $SSH_USER --password $SSH_PASS sftp://$SSH_HOST

  ssh -t $SSH_USER@$SSH_HOST << EOF
  cd "$REMOTE_PATH"
  mv "$THEME_NAME" "$THEME_NAME.$NOW"
  tar -xzf "$DEPLOY_NAME"
  echo "$SSH_PASS" | sudo -S chown -R www-data:www-data "$THEME_NAME"
  rm "$DEPLOY_NAME"
  exit
EOF
fi

if [[ $STAGE = 'dev' || $STAGE = 'live']]; then
  lftp -e "set sftp:connect-program 'ssh -a -x -i $SSH_KEY_FILE'; connect sftp://$SSH_USER@$SSH_HOST:$SSH_PORT; put -O $REMOTE_PATH $DEPLOY_NAME; bye"

  ssh -t -t -x -i $SSH_KEY_FILE -p $SSH_PORT $SSH_USER@$SSH_HOST << EOF
  cd "$REMOTE_PATH"
  mv "$THEME_NAME" "$THEME_NAME.$NOW"
  tar -xzf "$DEPLOY_NAME"
  rm "$DEPLOY_NAME"
  exit
EOF
fi

rm $DEPLOY_NAME

OUT=$?

if [ $OUT != 0 ]; then
  echo 'Deploy failed'
  exit 1
fi

echo 'Deploy successful'

date
echo
