#!/usr/bin/env bash

set -e

echo "Waiting for MySQL..."
while ! nc -z ${MYSQL_HOST} ${MYSQL_PORT}; do sleep 1; done

echo "Installing..."
mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} --password="${MYSQL_PASSWORD}" ${MYSQL_DATABASE} < \
    /var/www/html/source/Setup/Sql/database_schema.sql
mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} --password="${MYSQL_PASSWORD}" ${MYSQL_DATABASE} < \
    /var/www/html/vendor/oxid-esales/oxideshop-demodata-ce/src/demodata.sql

echo "Generating views..."
/var/www/html/vendor/bin/oe-eshop-db_views_generate

echo "Starting Apache..."
apache2-foreground
