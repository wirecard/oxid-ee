#!/usr/bin/env bash

set -ex

cd ${WEBROOT_DIR}/modules/${MODULE_PATH}
composer cs-fix
