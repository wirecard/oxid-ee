#!/bin/bash

set -ex
export TRAVIS_PHP_VERSION=7.1
export CODACY_PROJECT_TOKEN=f0c56561045b4e57bb00e2107bdae270

export MYSQL_USER=root
export MYSQL_PASSWORD=root
export MYSQL_DATABASE=oxid_db
export OXID_CONTAINER=oxid_ee_web
export OXID_VERSION=dev-b-6.1-ce
export OXID_SERVICE=web
export MODULE_NAME=wirecard/oxid-ee
export MODULE_PATH=wirecard/paymentgateway

docker-compose -f docker-compose.ci.yml up -d --build
timeout 60 bash -c 'while [[ "$(docker exec ${OXID_CONTAINER} curl -Ifs -o /dev/null -w ''%{http_code}'' http://localhost)" != "200" ]]; do sleep 2; done' || false

docker exec ${OXID_CONTAINER} phpcs.sh
docker exec ${OXID_CONTAINER} phpmd.sh
docker exec ${OXID_CONTAINER} runtests-unit-coverage.sh
docker exec ${OXID_CONTAINER} bash -c 'reset-shop.sh && runtests-selenium.sh'

docker-compose -f docker-compose.ci.yml kill
docker-compose -f docker-compose.ci.yml down
