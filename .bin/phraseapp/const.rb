# frozen_string_literal: true

module Const
  GITHUB_PHRASEAPP_PR_TITLE = '[PhraseApp] Update locales'
  GITHUB_PHRASEAPP_PR_BODY = 'Update locales from PhraseApp'
  GIT_PHRASEAPP_COMMIT_MSG = '[skip ci] Update translations from PhraseApp'
  GIT_PHRASEAPP_BRANCH_BASE = 'master'
  PHRASEAPP_PROJECT_ID = '9036e89959d471e0c2543431713b7ba1'
  PHRASEAPP_FALLBACK_LOCALE = 'en_US'
  PHRASEAPP_TAG = 'oxid'
  LOCALE_PREFIX = 'wdpg_'

  # project-specific mappings for locales, see https://translate.oxidforge.org/ for available OXID locales
  LOCALE_MAP = {
    en_US: ['en', 'English'],
    de_DE: ['de', 'Deutsch'],
  }.freeze

  LOCALE_FILE_HEADER = <<-EOF
<?php
/**
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*/
  EOF

  # paths relative to project root
  PLUGIN_DIR = ''
  PLUGIN_I18N_DIRS = [
    File.join(PLUGIN_DIR, 'translations'),
    File.join(PLUGIN_DIR, 'views', 'admin'),
  ].freeze
end
