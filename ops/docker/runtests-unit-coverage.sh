#!/usr/bin/env bash

set -ex

# run tests
cd /var/www/html
vendor/bin/runtests-coverage

# upload coverage
cd /var/www/html/source/modules/${MODULE_PATH}
composer upload-coverage
