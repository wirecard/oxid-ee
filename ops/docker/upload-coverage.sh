#!/usr/bin/env bash

set -ex

# upload coverage
cd /var/www/html/source/modules/${MODULE_PATH}
composer upload-coverage
