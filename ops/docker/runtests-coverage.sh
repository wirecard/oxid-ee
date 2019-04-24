#!/usr/bin/env bash

set -ex

# create coverage report
cd /var/www/html
vendor/bin/runtests-coverage
