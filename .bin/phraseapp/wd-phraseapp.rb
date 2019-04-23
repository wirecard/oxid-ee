using Rainbow

# Methods to handle PhraseApp locales
class WdPhraseApp
  def initialize()
    @log = Logger.new(STDOUT, level: Env::DEBUG ? 'DEBUG' : 'INFO')
    credentials = PhraseApp::Auth::Credentials.new(token: Env::PHRASEAPP_TOKEN, debug: Env::DEBUG)
    @phraseapp = PhraseApp::Client.new(credentials)
    @translation_builder = TranslationBuilder.new
    @tmp_path = File.join(Dir.pwd, '.bin', 'phraseapp', 'tmp')
    @branch_name

    @phraseapp_id              = Const::PHRASEAPP_PROJECT_ID
    @phraseapp_fallback_locale = Const::PHRASEAPP_FALLBACK_LOCALE
    @phraseapp_tag             = Const::PHRASEAPP_TAG
    @locale_map                = Const::LOCALE_MAP
    @plugin_i18n_dirs          = Const::PLUGIN_I18N_DIRS
  end

  # Creates a branch on PhraseApp & pushes keys to it.
  def push_to_branch
    create_branch && push_keys
  end

  # Returns an array of locale ids available on the PhraseApp project.
  def get_locale_ids
    # PhraseApp has a limit of 100 items per page on this paginated endpoint.
    locales, err = @phraseapp.locales_list(@phraseapp_id, 1, 100, OpenStruct.new)
    if err.nil?
      locales = locales.map { |l| l.name }
      @log.info('Retrieved list of locales.')
      @log.info(locales)
      return locales
    else
      @log.error('An error occurred while getting locales from PhraseApp.'.red.bright)
      @log.debug(err)
      exit(1)
    end
  end

  # Downloads locale files for all locale ids into the plugin i18n dir and generate translation files for plugin.
  def pull_locales
    @log.info('Downloading locales...'.cyan.bright)

    get_locale_ids.each do |id|
      # locale map should contain a mapping for the current id
      unless @locale_map.key?(id.to_sym)
        @log.warn("Skipped #{id} because no locale mapping is defined.".yellow.bright)
        next
      end

      mapped_id = @locale_map[id.to_sym][0]
      file_basename = "module_#{mapped_id}_lang.json"
      phraseapp_json = pull_locale(id)

      @plugin_i18n_dirs.each do |dir|
        # make subfolder for locale, if needed
        locale_subfolder = File.join(Dir.pwd, dir, mapped_id)
        FileUtils.mkdir(locale_subfolder) unless File.directory?(locale_subfolder)
        # write
        File.write(File.join(locale_subfolder, file_basename), phraseapp_json)
      end

      @translation_builder.build(@locale_map[id.to_sym][1], file_basename)
    end
  end

  # Downloads a locale file into the plugin i18n dir.
  def pull_locale(id)
    params = OpenStruct.new({
      :encoding => 'UTF-8',
      :fallback_locale_id => @phraseapp_fallback_locale,
      :file_format => 'simple_json',
      :include_empty_translations => true,
      :include_translated_keys => true,
      :include_unverified_translations => true,
      :tags => @phraseapp_tag,
    })

    @log.info("Downloading locale files for #{id}...".bright)

    json, err = @phraseapp.locale_download(@phraseapp_id, id, params)
    if err.nil?
      return json
    else
      @log.error("An error occurred while downloading locale #{id}.json from PhraseApp.".red.bright)
      @log.debug(err)
      exit(1)
    end
  end

  # Returns branch name for use on PhraseApp. Uses normalized local git branch, prepended by the plugin tag.
  def get_branch_name
    local_branch_name = Git.open(Dir.pwd, :log => @log).current_branch
    "#{@phraseapp_tag}-#{local_branch_name.downcase.gsub(/(\W|_)/, '-')}"
  end

  # Creates branch on PhraseApp for the current git branch.
  def create_branch
    @branch_name = get_branch_name

    if !HighLine.agree("This will create branch '#{@branch_name}' on PhraseApp. Proceed? (y/n)".bright)
      @log.info('Aborted.')
      return false
    end

    begin
      _branch, err = @phraseapp.branch_create(@phraseapp_id, OpenStruct.new({ :name => @branch_name }))
      if err.nil?
        @log.info('Branch created.'.bright)
      else
        @log.error("An error occurred while creating branch on PhraseApp.".red.bright)
        @log.debug(err)
        exit(1)
      end
    rescue NoMethodError => e
      @log.warn('Request failed. Branch already exists.'.cyan.bright)
      @log.debug(e)
    end

    # Verify that branch was created.
    @log.info('Waiting for PhraseApp to be ready for us to push to the branch...')
    sleep(10)
  end

  # Uploads a JSON file with keys to the PhraseApp branch
  def push_keys
    WdProject.new.json_generate_from_worktree

    worktree_json = File.join(@tmp_path, 'worktree_keys.json')

    unless File.exist?(worktree_json)
      @log.fatal("Couldn't find the generated JSON file in #{worktree_json}".red.bright) && exit(1)
    end

    if @branch_name.empty?
      @log.fatal('PhraseApp branch name is not defined') && exit(1)
    end

    @log.info('Uploading to PhraseApp...')
    upload, err = @phraseapp.upload_create(@phraseapp_id, OpenStruct.new({
      :autotranslate => false,
      :branch => @branch_name,
      :file => worktree_json,
      :file_encoding => 'UTF-8',
      :file_format => 'simple_json',
      :locale_id => @phraseapp_fallback_locale,
      :tags => @phraseapp_tag,
      :update_descriptions => false,
      :update_translations => true,
    }))

    if err.nil?
      @log.info('Success! Uploaded to PhraseApp'.green.bright)
      @log.info(upload.summary)
    else
      @log.error('An error occurred while uploading to PhraseApp.'.red.bright)
      @log.debug(err)
      exit(1)
    end
  end
end
