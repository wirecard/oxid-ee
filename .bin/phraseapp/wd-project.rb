using Rainbow

# Project-specific helpers
class WdProject
  def initialize
    @log = Logger.new(STDOUT, level: Env::DEBUG ? 'DEBUG' : 'INFO')
    @repo = Env::TRAVIS_REPO_SLUG
    @head = Env::TRAVIS_BRANCH
    @translation_builder = TranslationBuilder.new

    @worktree_keys = []
    @phraseapp_keys = []

    @phraseapp = WdPhraseApp.new
    @phraseapp_fallback_locale = Const::PHRASEAPP_FALLBACK_LOCALE
    @locale_prefix             = Const::LOCALE_PREFIX
    @locale_map = Const::LOCALE_MAP
    @tmp_path = File.join(Dir.pwd, '.bin', 'phraseapp', 'tmp')
  end

  # Returns true if source code has modified keys compared to the downloaded locale file of the fallback locale id
  def worktree_has_significant_key_changes?
    json_generate_from_worktree && json_pull_from_phraseapp && has_significant_key_changes?
  end

  # Compares the keys from source and PhraseApp.
  # Returns true on significant key diffs (keys missing from PhraseApp), false otherwise.
  # Simply warns on insignificant key diffs (keys present on PhraseApp but missing from project).
  def has_significant_key_changes?
    worktree_json = File.read(File.join(@tmp_path, 'worktree_keys.json'), :encoding => 'utf-8')
    @worktree_keys = JSON.parse(worktree_json).keys

    phraseapp_json = File.read(File.join(@tmp_path, 'phraseapp_keys.json'), :encoding => 'utf-8')
    @phraseapp_keys = JSON.parse(phraseapp_json).keys

    @log.info("Number of keys in worktree: #{@worktree_keys.length}")
    @log.info("Number of keys in PhraseApp: #{@phraseapp_keys.length}")

    # keys are unique; we use the intersection to detect differences
    has_key_changes = (@worktree_keys.length != @phraseapp_keys.length) || (@worktree_keys & @phraseapp_keys != @worktree_keys)

    unless has_key_changes
      @log.info('PhraseApp is fully synced with the current worktree.'.green.bright)
      return false
    end

    @log.warn('PhraseApp is not in sync with the current worktree.'.yellow.bright)

    @log.info('Checking for keys missing in the worktree (insignificant)...')
    has_keys_missing_in_worktree?

    @log.info('Checking for keys missing in PhraseApp (significant)...')
    if has_keys_missing_in_phraseapp?
      return true
    end

    false
  end

  def has_keys_missing_in_worktree?
    keys_missing_in_worktree = @phraseapp_keys - @worktree_keys
    if keys_missing_in_worktree.empty?
      @log.info('All keys present in PhraseApp are also present in the current worktree.'.green.bright)
      return false
    end

    @log.warn('Keys present in PhraseApp, but missing in the current worktree:'.yellow.bright)
    keys_missing_in_worktree.sort.each do |key|
      @log.warn("#{key}".bright)
    end
    true
  end

  def has_keys_missing_in_phraseapp?
    keys_missing_in_phraseapp = @worktree_keys - @phraseapp_keys
    if keys_missing_in_phraseapp.empty?
      @log.info('All keys present in the working tree are also present in PhraseApp.'.green.bright)
      return false
    end

    @log.warn('Keys present in the current worktree, but missing in PhraseApp:'.yellow.bright)
    keys_missing_in_phraseapp.sort.each do |key|
      @log.warn("#{key}".bright)
    end
    true
  end

  # Generates a json file with all keys present in the current working tree
  def json_generate_from_worktree
    @log.info('Gathering keys from local worktree into a temporary JSON file...')

    worktree_keys = @translation_builder.get_all_keys

    keys = []
    worktree_keys.each do |key|
      keys.push(key.sub(@locale_prefix, ''))
    end

    h = Hash[keys.map { |x| [x, ''] }]
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
