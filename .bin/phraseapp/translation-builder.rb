class TranslationBuilder
  def initialize
    @log = Logger.new(STDOUT, level: Env::DEBUG ? 'DEBUG' : 'INFO')
    @plugin_dir         = Const::PLUGIN_DIR
    @plugin_i18n_dirs   = Const::PLUGIN_I18N_DIRS
    @locale_file_header = Const::LOCALE_FILE_HEADER
  end

  def build(locale_name, file_basename)
    @plugin_i18n_dirs.each do |dir|
      abs_path = File.join(Dir.pwd, dir)
      Dir[File.join(abs_path, '**', file_basename)].each do |json_path|
        json_content = File.read(json_path, :encoding => 'utf-8')
        translations = JSON.parse(json_content)

        php_path = json_path.sub('.json', '.php')
        php_file = File.open(php_path, 'w:utf-8')

        php_file.puts(@locale_file_header + "\n")
        php_file.puts("$sLangName = '#{locale_name}';\n\n")

        php_file.puts('$aLang = array(')
        # specify charset in the array
        php_file.puts("    'charset' => 'UTF-8',")
        write_translations_to_php(translations, php_file)
        php_file.puts(");\n")

        php_file.close
        @log.info("Built translation file #{php_path}")
      end
    end
  end

  def get_all_keys
    keys = []

    get_needed_php_files.each do |file_path|
      keys += extract_keys_from_php_file(file_path)
    end

    get_needed_tpl_files.each do |file_path|
      keys += extract_keys_from_tpl_file(file_path)
    end

    keys.uniq
  end

  def extract_keys_from_php_file(file_path)
    file_content = File.read(file_path, :encoding => 'utf-8')
    keys = file_content.scan(/translate\(['"]([^'"]+)['"]\)/).flatten
    keys += file_content.scan(/translateString\(['"]([^'"]+)['"]\)/).flatten
    # reject OXID internal keys (all uppercase)
    keys.reject { |k| k =~ /^[A-Z_]+$/ }
  end

  def extract_keys_from_tpl_file(file_path)
    file_content = File.read(file_path, :encoding => 'utf-8')
    keys = file_content.scan(/oxmultilang ident="([^"]+)"/).flatten
    # reject OXID internal keys (all uppercase)
    keys.reject { |k| k =~ /^[A-Z_]+$/ }
  end

  def get_needed_php_files
    ignored_dirs = [
      'vendor',
      'translations',
      File.join('views', 'admin'),
    ]

    Dir.glob(File.join(Dir.pwd, @plugin_dir, '**', '*.php')).reject do |path|
      ignored_dirs.any? { |ignored| path =~ /\/#{ignored}\// }
    end
  end

  def get_needed_tpl_files
    Dir.glob(File.join(Dir.pwd, @plugin_dir, '**', '*.tpl'))
  end

  def write_translations_to_php(translations, php_file)
    translations.each do |key, value|
      if value.is_a?(Hash)
        value = value.values[0]
      end

      if value.nil? then value = '' end

      # strip whitespace and escape quotes
      line = "    '#{key}' => '#{value.strip.gsub("'") { "\\'" }}',"
      php_file.puts(line)
    end
  end
end
