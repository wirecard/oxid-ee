#!/usr/bin/env bash

set -ex

# run tests
cd /var/www/html

RESTORE_SHOP_AFTER_TESTS_SUITE=1 \
    vendor/bin/runtests
