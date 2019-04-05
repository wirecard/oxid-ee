#!/usr/bin/env bash

set -ex

# run tests
cd /var/www/html

# We're disabling the install/restore functionality of this OXID script because
# of path resolution issues during runtime. We invoke reset-shop.db before
# running Selenium tests.
INSTALL_SHOP=0 \
RESTORE_AFTER_ACCEPTANCE_TESTS=0 \
RESTORE_SHOP_AFTER_TESTS_SUITE=0 \
ACTIVATE_ALL_MODULES=1 \
SELENIUM_SERVER_IP=selenium \
SHOP_URL=http://${OXID_SERVICE} \
    vendor/bin/runtests-selenium
