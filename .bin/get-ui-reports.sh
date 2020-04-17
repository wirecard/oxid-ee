#!/bin/bash
# Shop System SDK:
# - Terms of Use can be found under:
# https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
# - License can be found under:
# https://github.com/wirecard/oxid-ee/blob/master/LICENSE

docker cp ${OXID_CONTAINER}:/var/www/html/source/modules/${MODULE_PATH}/Tests/${UI_TEST_JUNIT_REPORT_FILE_NAME} .
docker cp ${OXID_CONTAINER}:/var/www/html/source/modules/${MODULE_PATH}/Tests/${UI_TEST_HTML_REPORT_FILE_NAME} .
