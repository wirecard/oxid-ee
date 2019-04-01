#!/usr/bin/env bash
#
# Purpose: Reset the shop to a clean state with the default demo data
# Triggered: Between consecutive test suite executions

set -e

mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} --password="${MYSQL_PASSWORD}" << EOF
    DROP DATABASE IF EXISTS ${MYSQL_DATABASE};
    CREATE DATABASE ${MYSQL_DATABASE};
EOF

mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} --password="${MYSQL_PASSWORD}" ${MYSQL_DATABASE} < \
    /var/www/html/source/Setup/Sql/database_schema.sql

mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} --password="${MYSQL_PASSWORD}" ${MYSQL_DATABASE} < \
    /var/www/html/vendor/oxid-esales/oxideshop-demodata-ce/src/demodata.sql

rm -rf /var/www/html/source/tmp/*

echo "Shop restored to clean state!"
