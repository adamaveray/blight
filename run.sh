#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
APP_PATH="${DIR}/Blight.phar"

# Load system paths
PATH_POSTS="${DIR}/$( php -f ${APP_PATH} config:paths.posts )"
PATH_PAGES="${DIR}/$( php -f ${APP_PATH} config:paths.pages )"
PATH_DRAFTS="${DIR}/$( php -f ${APP_PATH} config:paths.drafts )"
PATH_ASSETS="${DIR}/$( php -f ${APP_PATH} config:paths.assets )"

CHECK_SECONDS=60
UPDATE_LOG="${DIR}/$( php -f ${APP_PATH} config:paths.log )"

SCRIPT_LOCK_FILE="${DIR}/blight-updater.pid"
BASH_LOCK_DIR="${DIR}/blight-updater.sh.lock"


function finish {
	rmdir "$BASH_LOCK_DIR" 2>/dev/null
	exit
}

function update_site {
	php -f "${APP_PATH}" > /dev/null
}

function log {
	echo "[`date -u +%Y-%m-%d\ %k:%M:%S`] Updater.INFO: $1" >> "$UPDATE_LOG"
}

if [ -d "$BASH_LOCK_DIR" ]; then
	echo "Already running"
	exit
fi

# Create lock
mkdir "$BASH_LOCK_DIR" 2>/dev/null

trap finish INT TERM EXIT

log "Updating site"

# Run updater
update_site

if [ "`which inotifywait`" != "" ] ; then
	while true ; do
		inotifywait -q -q -r -t $CHECK_SECONDS -e close_write -e create -e delete -e moved_from --exclude "\.swp$" "$PATH_POSTS" "$PATH_PAGES" "$PATH_DRAFTS" "$PATH_ASSETS"

		if [ $? -eq 0 ] ; then
			log "Updating site - A source file changed"
		else
			log "Updating site - $CHECK_SECONDS seconds elapsed"
		fi

		update_site

		while [ $? -eq 2 ] ; do
			log "Updating site - last run performed writes"
			update_site
		done
	done
fi

trap - INT TERM EXIT
finish
