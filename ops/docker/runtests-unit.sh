#!/usr/bin/env bash

set -ex

# run tests
cd /var/www/html
vendor/bin/runtests
