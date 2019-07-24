#!/usr/bin/env bash

set -ex

# create coverage report
cd /var/www/html

COMMAND="vendor/bin/runtests-coverage"

# if requested, only create an XML clover
# this can be used to circumvent PHP warnings thrown by the HTML coverage generation
# see https://github.com/sebastianbergmann/php-code-coverage/issues/551
if [ "${OXID_COVERAGE_XML_ONLY}" = true ]
then
    REPORTS_DIR="${WEBROOT_DIR}/modules/${MODULE_PATH}/Tests/reports"
    rm -rf $REPORTS_DIR && mkdir -p -m 777 $REPORTS_DIR
    COMMAND="vendor/bin/runtests --coverage-clover $REPORTS_DIR/clover.xml AllTestsUnit"
fi

RESTORE_SHOP_AFTER_TESTS_SUITE=1 \
# suppress PHP warnings caused by PHPUnit
PHPBIN="php -d error_reporting=0" \
    $COMMAND
