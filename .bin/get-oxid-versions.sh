#!/bin/bash
# Shop System SDK:
# - Terms of Use can be found under:
# https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
# - License can be found under:
# https://github.com/wirecard/oxid-ee/blob/master/LICENSE


curl https://api.github.com/repos/OXID-eSales/oxideshop_project/branches  | jq -r '.[] | .name' | grep 'ce' | egrep -v 'beta' > tmp.txt

# sort versions in descending order
sort -nr tmp.txt > ${OXID_RELEASES_FILE}

if [[ $(git diff HEAD ${OXID_RELEASES_FILE}) != '' ]]; then
    git config --global user.name "Travis CI"
    git config --global user.email "wirecard@travis-ci.org"

    git add  ${OXID_RELEASES_FILE}
    git commit -m "${SHOP_SYSTEM_UPDATE_COMMIT}"
    git push --quiet https://${GITHUB_TOKEN}@github.com/${TRAVIS_REPO_SLUG} HEAD:master
fi
