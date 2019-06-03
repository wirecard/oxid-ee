class TranslationBuilder
  def initialize
    @log = Logger.new(STDOUT, level: Env::DEBUG ? 'DEBUG' : 'INFO')
    @plugin_dir         = Const::PLUGIN_DIR
    @plugin_i18n_dirs   = Const::PLUGIN_I18N_DIRS
    @locale_prefix      = Const::LOCALE_PREFIX
    @locale_file_header = Const::LOCALE_FILE_HEADER
  end

  # Builds PHP language files from all found JSON files
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

  # Returns an array of all keys used in the codebase
  def get_all_keys
    keys = []

    get_needed_php_files.each do |file_path|
      keys += extract_keys_from_php_file(file_path)
    end

    get_needed_tpl_files.each do |file_path|
      keys += extract_keys_from_tpl_file(file_path)
    end

    get_needed_xml_files.each do |file_path|
      keys += extract_keys_from_xml_file(file_path)
    end

    keys.uniq!
    check_keys(keys)
    keys
  end

  # Outputs a log warn for every incorrect named translation key
  def check_keys(keys)
    keys.each do |key|
      if key.index(@locale_prefix) != 0
        @log.warn("Key #{key} uses wrong prefix")
      end
    end
  end

  # Parses a PHP file and returns used keys based on a predefined regex match
  def extract_keys_from_php_file(file_path)
    file_content = File.read(file_path, :encoding => 'utf-8')
    keys = file_content.scan(/translate\(['"]([^'"]+)['"]/).flatten
    keys += file_content.scan(/translateString\(['"]([^'"]+)['"]/).flatten
    # reject OXID internal keys (all uppercase)
    keys.reject { |k| k =~ /^[A-Z_]+$/ }
  end

  # Parses a TPL file and returns used keys based on a predefined regex match
  def extract_keys_from_tpl_file(file_path)
    file_content = File.read(file_path, :encoding => 'utf-8')
    keys = file_content.scan(/oxmultilang ident="([^"]+)"/).flatten
    # reject OXID internal keys (all uppercase)
    keys.reject { |k| k =~ /^[A-Z_]+$/ }
  end

  # Parses an XML file and returns used keys based on the presence of a specific attribute
  def extract_keys_from_xml_file(file_path)
    file_content = File.read(file_path, :encoding => 'utf-8')
    doc = Nokogiri::XML(file_content)
    doc.xpath("//*[starts-with(@id, '#{@locale_prefix}')]").map { |node| node.attr('id') }
  end

  # Parse the metadata.php file for keys, this keys must not be prefixed because of a special handling from oxid
  def get_metadata_keys
    metadata_keys = extract_keys_from_php_file(File.join(Dir.pwd, 'metadata.php'))
    metadata_keys.uniq
  end

  # Returns an array of absolute paths to PHP files that should be parsed for keys
  def get_needed_php_files
    ignored_dirs = [
      'vendor',
      'translations',
      'metadata.php',
      'Tests',
      File.join('views', 'admin'),
    ]

    Dir.glob(File.join(Dir.pwd, @plugin_dir, '**', '*.php')).reject do |path|
      ignored_dirs.any? { |ignored| path =~ /\/#{Regexp.escape(ignored)}/ }
    end
  end

  # Returns an array of absolute paths to TPL files that should be parsed for keys
  def get_needed_tpl_files
    Dir.glob(File.join(Dir.pwd, @plugin_dir, '**', '*.tpl'))
  end

  # Returns an array of absolute paths to XML files that should be parsed for keys
  def get_needed_xml_files
    Dir.glob(File.join(Dir.pwd, 'menu.xml'))
  end

  # Writes translations (key-value pairs) to the given file, using valid PHP array syntax
  def write_translations_to_php(translations, php_file)
    metadata_keys = get_metadata_keys

    translations.each do |key, value|
      if value.is_a?(Hash)
        value = value.values[0]
      end

      if value.nil? then value = '' end

      prefix = metadata_keys.any? { |metadata_key| metadata_key == key } ? '' : @locale_prefix
      # strip whitespace and escape quotes
      line = "    '#{prefix}#{key}' => '#{value.strip.gsub("'") { "\\'" }}',"
      php_file.puts(line)
    end
  end
end
