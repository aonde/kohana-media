<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * @author Alexey Popov
 */
class Kohana_Media {

	/**
	 * Delimiter
	 *
	 * @var string
	 */
	public static $delimiter = '-----';

	/**
	 * Files to minify
	 *
	 * @var array
	 */
	protected $_files = array();

	/**
	 * Last modification times data array
	 *
	 * @var type
	 */
	protected $_mtimes = array();

	/**
	 * Outgoing headers
	 *
	 * @var type
	 */
	protected $_headers = array();

	/**
	 * External files array
	 *
	 * @var array
	 */
	protected $_external = array();

	/**
	 * Inline codes to minify
	 *
	 * @var type
	 */
	protected $_source = array();

	/**
	 * Config object
	 *
	 * @var array
	 */
	protected $_config;

	/**
	 * Get a singleton Media instance.
	 *
	 *     $media = Media::instance('js');
	 *
	 * @param   string  instance name
	 * @return  Media
	 */
	public static function instance($name)
	{
		$config = Kohana::config('media');

		if ( ! isset($config[$name]))
		{
			throw new Kohana_Exception('Couldn\'t find configuration for environment :name; check configuration file :file', array(
				':environment' => $name,
				':file'        => 'media'.EXT
			));
		}

		$config = $config[$name];

		return new $config['driver']($name, $config);
	}

	/**
	 * Class constructor
	 *
	 * @param array $config
	 */
	public function __construct($instance, array $config)
	{
		$this->_instance = $instance;
		$this->_config   = $config;
	}

	/**
	 * Add static file to minification
	 *
	 * @param    string  $file        Filename
	 * @param    array   $attributes  File attributes
	 * @return   Media
	 */
	public function add_file($file, array $attributes = NULL)
	{
		// Is it an external static file?
		if (UTF8::stristr($file, '://'))
		{
			$this->_external[$file] = $attributes;

			return $this;
		}

		if (is_file($this->_config['path'].$file))
		{
			$this->_files[$file]  = $attributes;
			$this->_mtimes[$file] = filemtime($this->_config['path'].$file);
		}

		return $this;
	}

	/**
	 * Add inline source
	 *
	 * @param    string   $text        Javascript text
	 * @param    array    $attributes  Attributes
	 * @return   Media
	 */
	public function add_source($text, array $attributes = NULL)
	{
		$this->_source[] = array
		(
			'text'       => $text,
			'attributes' => $attributes
		);

		return $this;
	}

	/**
	 * Returns a sequence of HTML tags to insert in the HTML-page
	 *
	 * @return string
	 */
	public function html_files()
	{
		$content = '';

		if ( (bool) $this->_config['merge'] === TRUE)
		{
			$filename = implode(self::$delimiter, array_keys($this->_files));

			$files[$filename] = NULL;
			$this->_mtimes[$filename] = max($this->_mtimes);
		}
		else
		{
			$files = $this->_files;
		}

		foreach ($files as $file => $attributes)
		{
			$content .= $this->_tag_file(Route::get('media')->uri(array(
				'environment' => $this->_instance,
				'file'        => $file,
				'mtime'       => $this->_mtimes[$file]
			)), $attributes);
		}

		foreach ($this->_external as $file => $attributes)
		{
			$content .= $this->_tag_file($file, $attributes);
		}

		return $content;
	}

	/**
	 * Returns a sequence of HTML tags to insert in the HTML-page
	 *
	 * @return string
	 */
	public function html_source()
	{
		$content = '';

		foreach ($this->_source as $source)
		{
			$content .= $this->_tag_source($this->_minify($source['text'], md5($source['text'])), $source['attributes']);
		}

		return $content;
	}

	/**
	 * Returns all HTML-tags
	 *
	 * @return string
	 */
	public function html()
	{
		return $this->html_files().$this->html_source();
	}

	/**
	 * Returns
	 *
	 * @return type
	 */
	public function filemtime()
	{
		return max($this->_mtimes);
	}

	/**
	 * Returns minified files content
	 *
	 * @return string
	 */
	public function minify_files()
	{
		$content = '';

		$files   = array_keys($this->_files);

		foreach ($files as $file)
		{
			$content .= $this->_minify_file($file);
		}

		return $content;
	}

	/**
	 * Returns headers array
	 *
	 * @return type
	 */
	public function headers()
	{
		$this->_headers['last-modified'] = date('r', $this->filemtime());
		$this->_headers['content-type']  = File::mime_by_ext($this->_instance);

		return $this->_headers;
	}

	protected function _tag_file($filename, array $attributes = NULL)
	{
		return $filename;
	}

	protected function _tag_source($text, array $attributes = NULL)
	{
		return $text;
	}

	protected function _minify($text)
	{
		return $text;
	}

	protected function _minify_file($filename)
	{
		$original = $this->_config['path'].$filename;
		$cached   = Kohana::$cache_dir.DIRECTORY_SEPARATOR.$this->_instance.DIRECTORY_SEPARATOR.$filename;

		if ( ! is_file($cached) OR
			filemtime($cached) < filemtime($original))
		{
			if ( ! is_dir(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$this->_instance))
			{
				mkdir(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$this->_instance);
			}

			$content = $this->_minify(file_get_contents($original));
			$this->_save($cached, $content);

			return $content;
		}

		return file_get_contents($cached);
	}

	protected function _save($file, $data)
	{
		// If caching is off - don't save minified data
		if ( ! Kohana::$caching OR ! $this->_config['cache'])
		{
			return;
		}

		// Creating empty file if it is not exists
		// If exists - this operation will make no harm to it
		fclose(fopen($file, "a+b"));

		// File blocking
		if( ! ($f = fopen($file, "r+b")))
		{
			throw new Kohana_Exception('Can\'t open cache file :file', array(
				':file' => $file
			));
		}

		// Waiting a monopole owning
		flock($f, LOCK_EX);

		// Writing file
		fwrite($f, $data);

		fclose($f);
	}

	/**
	 * Returns all HTML content
	 *
	 * @uses   Media::html()
	 * @return type
	 */
	function __toString()
	{
		try
		{
			$this->html();
		}
		catch (Exception $e)
		{
			return '<!-- '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine().' -->';
		}
	}

} // End Kohana_Media