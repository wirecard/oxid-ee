#!/usr/bin/env bash

set -ex

# run tests
cd /var/www/html

# We're disabling the install functionality of this OXID script because of path
# resolution issues during runtime. We invoke reset-shop.db before running
# Selenium tests.
INSTALL_SHOP=0 \
RETRY_TIMES_AFTER_TEST_FAIL=0 \
SELENIUM_SERVER_IP=selenium \
SHOP_URL=http://${OXID_SERVICE} \
PHPBIN="php -d error_reporting=0" \
    vendor/bin/runtests-selenium
