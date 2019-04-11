# frozen_string_literal: true

require 'fileutils'
require 'git'
require 'highline'
require 'json'
require 'logger'
require 'nokogiri'
require 'octokit'
require 'phraseapp-ruby'
require 'rainbow/refinement'
require_relative '.bin/phraseapp/const.rb'
require_relative '.bin/phraseapp/env.rb'
require_relative '.bin/phraseapp/wd-git.rb'
require_relative '.bin/phraseapp/wd-github.rb'
require_relative '.bin/phraseapp/wd-project.rb'
require_relative '.bin/phraseapp/wd-phraseapp.rb'
require_relative '.bin/phraseapp/translation-builder.rb'

using Rainbow

OXID_CONTAINER = 'oxid_ee_web'

desc 'Start containers'
task :up do
  sh "OXID_CONTAINER=#{OXID_CONTAINER} docker-compose up --build -d"
end

desc 'Tear down containers'
task :down do
  sh "OXID_CONTAINER=#{OXID_CONTAINER} docker-compose down || true"
end

desc 'Bash into the web container'
task :bash do
  sh "docker exec -it #{OXID_CONTAINER} bash"
end

desc 'Show web container logs'
task :logs do
  sh "docker logs -f #{OXID_CONTAINER}"
end

desc 'Show OXID logs'
task :oxlogs do
  sh "docker exec #{OXID_CONTAINER} tail -f /var/www/html/source/log/oxideshop.log"
end

desc 'Regenerate views'
task :views do
  sh "docker exec #{OXID_CONTAINER} /var/www/html/vendor/bin/oe-eshop-db_views_generate"
end

desc 'Reset shop (restore demo data & delete tmp files)'
task :reset_shop do
  sh "docker exec #{OXID_CONTAINER} reset-shop.sh"
  Rake::Task['views'].invoke
end

desc 'Run PHP Mess Detector'
task :md do
  sh "docker exec #{OXID_CONTAINER} phpmd.sh"
end

desc 'Run PHP CodeSniffer'
task :cs_check do
  sh "docker exec #{OXID_CONTAINER} phpcs.sh"
end

desc 'Run PHPUnit tests'
task :runtests_unit do
  sh "docker exec #{OXID_CONTAINER} runtests-unit.sh"
end

desc 'Run Selenium tests'
task :runtests_selenium do
  sh "docker exec #{OXID_CONTAINER} runtests-selenium.sh"
end

#-------------------------------------------------------------------------------
# PhraseApp tasks
#-------------------------------------------------------------------------------
namespace :phraseapp do
  desc 'Pull locale files'
  task :pull do
    WdPhraseApp.new.pull_locales
  end

  desc 'Parse translatable keys and push to a PhraseApp branch'
  task :push do
    if WdProject.new.worktree_has_key_changes?
      WdPhraseApp.new.push_to_branch
    end
  end

  desc '[CI] Pull locales, commit & push to git remote'
  task :ci_update do
    WdPhraseApp.new.pull_locales && WdProject.new.commit_push_pr_locales
  end

  desc '[CI] Check if PhraseApp is up to date with the project'
  task :ci_check_if_in_sync do
    if WdProject.new.worktree_has_key_changes?
      puts 'PhraseApp is not in sync with the current commit. Exiting.'.red.bright
      exit(1)
    end
  end
end
