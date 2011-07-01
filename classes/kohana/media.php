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

	// Common priority type constants
	const PRIORITY_LOW      = 1;
	const PRIORITY_MEDIUM   = 2;
	const PRIORITY_HIGH     = 3;
	const PRIORITY_CRITICAL = 4;

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
			throw new Kohana_Exception('Couldn\'t find configuration for environment :environment; check configuration file :file', array(
				':environment' => $name,
				':file'        => 'media'.EXT
			));
		}

		$config   = $config[$name];

		$class    = 'Media_'.UTF8::ucfirst($name);
		$filename = UTF8::str_ireplace('_', DIRECTORY_SEPARATOR, UTF8::strtolower($name));

		if ( ! (Kohana::find_file('classes', 'media'.DIRECTORY_SEPARATOR.$filename)))
		{
			throw new Kohana_Exception('Couldn\'t find media driver :class for environment :environment', array(
				':environment' => $name,
				':class'       => $class
			));
		}

		return new $class($name, $config);
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
	 * @param    string   $file          Filename
	 * @param    integer  $priority      Order priority
	 * @param    array    $attributes    File attributes
	 * @return   Media
	 */
	public function add_file($file, $priority = Media::PRIORITY_MEDIUM, array $attributes = NULL)
	{
		$priority = $this->_check_priority($priority);

		// Is it an external static file?
		if (strpos($file, '://') !== FALSE)
		{
			$this->_external[$priority][$file] = $attributes;

			return $this;
		}

		if (is_file($this->_config['path'].$file) AND // File exist
			! isset($this->_mtimes[$file]))  // We don't need duplicates
		{
			$this->_files[$priority][$file] = $attributes;
			$this->_mtimes[$file]           = filemtime($this->_config['path'].$file);
		}

		return $this;
	}

	/**
	 * Add inline source
	 *
	 * @param    string   $text        Javascript text
	 * @param    integer  $priority    Order priority
	 * @param    array    $attributes  Attributes
	 * @return   Media
	 */
	public function add_source($text, $priority = Media::PRIORITY_MEDIUM, array $attributes = NULL)
	{
		$priority = $this->_check_priority($priority);

		$this->_source[$priority][] = array
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

		// External files first
		if (sizeof($this->_external) > 0)
		{
			arsort($this->_external);

			$external = call_user_func_array('array_merge', $this->_external);

			foreach ($external as $file => $attributes)
			{
				$content .= $this->_tag_file($file, $attributes);
			}
		}

		// Then local files
		if (sizeof($this->_files) > 0)
		{
			arsort($this->_files);

			$files = call_user_func_array('array_merge', $this->_files);

			if ($this->_config['merge'] === TRUE)
			{
				if (sizeof($this->_mtimes) > 0)
				{
					$files = implode(self::$delimiter, array_keys($files));

					$this->_mtimes[$files] = max($this->_mtimes);

					$files = array($files => NULL);
				}
			}

			$path = DOCROOT.'media'.DIRECTORY_SEPARATOR.$this->_instance.DIRECTORY_SEPARATOR;

			foreach ($files as $file => $attributes)
			{
				if (is_file($path.$file) AND
					filemtime($path.$file) < $this->_mtimes[$file])
				{
					@unlink($path.$file);
				}

				$content .= $this->_tag_file(Route::get('media')->uri(array(
					'environment' => $this->_instance,
					'file'        => $file,
					'mtime'       => $this->_mtimes[$file]
				)), $attributes);
			}
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
		if (sizeof($this->_source) == 0) return;

		$content = '';

		arsort($this->_source);

		$source = call_user_func_array('array_merge', $this->_source);

		foreach ($source as $s)
		{
			$content .= $this->_tag_source($this->_minify($s['text'], md5($s['text'])), $s['attributes']);
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
		$html = (string) $this->html_files().$this->html_source();

		$this->flush();

		return $html;
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
	public function minify_files($filename)
	{
		$files = explode(self::$delimiter, $filename);

		if ( ! is_array($files))
		{
			$files = array($files);
		}

		foreach ($files as $file)
		{
			$this->add_file($file);
		}

		if (sizeof($this->_files) == 0)
		{
			return;
		}

		$content = '';

		$files = call_user_func_array('array_merge', $this->_files);

		foreach (array_keys($files) as $file)
		{
			$content .= $this->_minify_file($file);
		}

		$this->_save($filename, $content);

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

	/**
	 * Flushes files- and source array
	 *
	 * @return void
	 */
	public function flush()
	{
		$this->_files  = array();
		$this->_source = array();
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
		return $this->_minify(file_get_contents($this->_config['path'].$filename));
	}

	protected function _check_priority($priority)
	{
		return (in_array( (int) $priority, array(1, 2, 3, 4))) ? $priority : 2;
	}

	protected function _save($file, $data)
	{
		// If caching is off - don't save minified data
		if ( ! $this->_config['cache'])
		{
			return;
		}

		$file = DOCROOT.'media'.DIRECTORY_SEPARATOR.$this->_instance.DIRECTORY_SEPARATOR.$file;

		if ( ! is_dir(pathinfo($file, PATHINFO_DIRNAME)))
		{
			mkdir(pathinfo($file, PATHINFO_DIRNAME));
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
			return $this->html();
		}
		catch (Exception $e)
		{
			return '<!-- '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine().' -->';
		}
	}

} // End Kohana_Media