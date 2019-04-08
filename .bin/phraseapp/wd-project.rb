using Rainbow

# Project-specific helpers
class WdProject
  def initialize
    @log = Logger.new(STDOUT, level: Env::DEBUG ? 'DEBUG' : 'INFO')
    @repo = Env::TRAVIS_REPO_SLUG
    @head = Env::TRAVIS_BRANCH
    @translation_builder = TranslationBuilder.new

    @phraseapp = WdPhraseApp.new
    @phraseapp_fallback_locale = Const::PHRASEAPP_FALLBACK_LOCALE
    @locale_map = Const::LOCALE_MAP
    @tmp_path = File.join(Dir.pwd, '.bin', 'phraseapp', 'tmp')
  end

  # Returns true if source code has modified keys compared to the downloaded locale file of the fallback locale id
  def worktree_has_key_changes?
    json_generate_from_worktree && json_pull_from_phraseapp && has_key_changes?
  end

  # Compares the keys from source and PhraseApp and returns true if they have any difference in keys, false otherwise.
  def has_key_changes?
    worktree_json = File.read(File.join(@tmp_path, 'worktree_keys.json'), :encoding => 'utf-8')
    worktree_keys = JSON.parse(worktree_json).keys

    phraseapp_json = File.read(File.join(@tmp_path, 'phraseapp_keys.json'), :encoding => 'utf-8')
    phraseapp_keys = JSON.parse(phraseapp_json).keys

    @log.info("Number of keys in worktree: #{worktree_keys.length}")
    @log.info("Number of keys on PhraseApp: #{phraseapp_keys.length}")

    # keys are unique; we use the intersection to detect differences
    has_key_changes = (worktree_keys.length != phraseapp_keys.length) || (worktree_keys & phraseapp_keys != worktree_keys)

    if has_key_changes
      @log.warn('Changes to translatable keys have been detected in the working tree.'.yellow.bright)
      @log.warn('Keys present on PhraseApp, but missing in the project:'.yellow.bright)
      (phraseapp_keys - worktree_keys).sort.each do |key|
        @log.warn("#{key}".bright)
      end

      @log.warn('Keys present in the project, but missing on PhraseApp:'.yellow.bright)
      (worktree_keys - phraseapp_keys).sort.each do |key|
        @log.warn("#{key}".bright)
      end
      return true
    end

    @log.info('No changes to translatable keys have been detected in the working tree.'.green.bright)
    false
  end

  # Generates a json file with all keys present in the current working tree
  def json_generate_from_worktree
    @log.info('Gathering keys from local worktree into a temporary JSON file...')

    worktree_keys = @translation_builder.get_all_keys
    h = Hash[worktree_keys.map { |x| [x, ''] }]
    f = File.join(@tmp_path, 'worktree_keys.json')
    File.write(f, JSON.pretty_generate(h), :encoding => 'utf-8')
  end

  # Pulls a json file with all keys from PhraseApp
  def json_pull_from_phraseapp
    @log.info('Pulling a fresh copy of translations from PhraseApp into a temporary JSON file...')

    phraseapp_json = @phraseapp.pull_locale(@phraseapp_fallback_locale)
    f = File.join(@tmp_path, 'phraseapp_keys.json')
    File.write(f, phraseapp_json)
  end

  # Adds, commits, pushes to remote any modified/untracked files in the i18n dir. Then creates a PR.
  def commit_push_pr_locales()
    paths = Const::PLUGIN_I18N_DIRS
    base = Const::GIT_PHRASEAPP_BRANCH_BASE
    commit_msg = Const::GIT_PHRASEAPP_COMMIT_MSG
    pr_title = Const::GITHUB_PHRASEAPP_PR_TITLE
    pr_body = Const::GITHUB_PHRASEAPP_PR_BODY

    paths.each do |path|
      WdGit.new.commit_push(@repo, @head, path, commit_msg)
    end

    WdGithub.new.create_pr(@repo, base, @head, pr_title, pr_body)
  end
end
