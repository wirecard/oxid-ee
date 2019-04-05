# frozen_string_literal: true

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
