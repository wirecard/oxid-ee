#!/usr/bin/env bash

set -ex

# run tests
cd /var/www/html

ACTIVATE_ALL_MODULES=1 \
INSTALL_SHOP=0 \
RESTORE_AFTER_ACCEPTANCE_TESTS=0 \
RESTORE_SHOP_AFTER_TESTS_SUITE=0 \
SELENIUM_SERVER_IP=selenium \
SHOP_URL=http://${OXID_SERVICE} \
    vendor/bin/runtests-selenium
