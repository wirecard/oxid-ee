#!/usr/bin/env bash

set -e

echo "Waiting for MySQL..."
while ! nc -z ${MYSQL_HOST} 3306; do sleep 1; done

echo "Installing..."
mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} --password="${MYSQL_PASSWORD}" ${MYSQL_DATABASE} < \
    /var/www/html/source/Setup/Sql/database_schema.sql

if [ "${OXID_DEMODATA}" = true ]
then
    echo "Importing demo data..."
    mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} --password="${MYSQL_PASSWORD}" ${MYSQL_DATABASE} < \
        /var/www/html/vendor/oxid-esales/oxideshop-demodata-ce/src/demodata.sql
fi

echo "Generating views..."
/var/www/html/vendor/bin/oe-eshop-db_views_generate

echo "Starting Apache..."
apache2-foreground
