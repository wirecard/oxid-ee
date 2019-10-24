#!/bin/bash
# Shop System SDK:
# - Terms of Use can be found under:
# https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
# - License can be found under:
# https://github.com/wirecard/oxid-ee/blob/master/LICENSE

PREVIEW_LINK='https://raw.githack.com/wirecard/reports'
REPORT_FILE='report.html'
#choose slack channel depending on the gateway
if [[ ${GATEWAY} = "API-WDCEE-TEST" ]]; then
  CHANNEL='shs-ui-api-wdcee-test'
elif [[  ${GATEWAY} = "API-TEST" ]]; then
   CHANNEL='shs-ui-api-test'
elif [[  ${GATEWAY} = "NOVA" ]]; then
   CHANNEL='shs-ui-nova'
fi

#send information about the build
curl -X POST -H 'Content-type: application/json' \
    --data "{'text': 'Build Failed. Oxid version: ${OXID_VERSION}\n
    Build URL : ${TRAVIS_JOB_WEB_URL}\n
    Build Number: ${TRAVIS_BUILD_NUMBER}\n
    Branch: ${BRANCH_FOLDER}', 'channel': '${CHANNEL}'}" ${SLACK_ROOMS}


# send link to the report into slack chat room
curl -X POST -H 'Content-type: application/json' --data "{
    'attachments': [
        {
            'fallback': 'Failed test data',
            'text': 'There are failed tests.
             Test report: ${PREVIEW_LINK}/${SCREENSHOT_COMMIT_HASH}/${RELATIVE_REPORTS_LOCATION}/${REPORT_FILE} .
             All screenshots can be found  ${REPO_LINK}/tree/${SCREENSHOT_COMMIT_HASH}/${RELATIVE_REPORTS_LOCATION} .',
            'color': '#764FA5'
        }
    ], 'channel': '${CHANNEL}'
}"  ${SLACK_ROOMS};
